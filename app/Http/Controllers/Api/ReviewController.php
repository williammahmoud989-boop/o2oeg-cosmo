<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Get reviews for a specific salon.
     */
    public function index(Salon $salon)
    {
        $reviews = $salon->reviews()
            ->with('user:id,name')
            ->where('is_public', true)
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    /**
     * Store a new review for a booking.
     */
    public function store(Request $request, Booking $booking)
    {
        // 1. Authorization
        if ($booking->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'Only completed bookings can be reviewed.'], 422);
        }

        // 2. Already reviewed?
        if ($booking->review()->exists()) {
            return response()->json(['message' => 'This booking has already been reviewed.'], 422);
        }

        // 3. Validation
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated, $booking) {
            $review = Review::create([
                'user_id' => auth()->id(),
                'salon_id' => $booking->salon_id,
                'booking_id' => $booking->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);

            // 4. Update Salon Aggregates
            $salon = $booking->salon;
            $newTotalReviews = $salon->total_reviews + 1;
            
            // Recalculate average: (OldTotal * OldRating + NewRating) / NewTotal
            $newRating = (($salon->total_reviews * $salon->rating) + $validated['rating']) / $newTotalReviews;

            $salon->update([
                'rating' => round($newRating, 2),
                'total_reviews' => $newTotalReviews
            ]);

            return response()->json([
                'message' => 'Review submitted successfully!',
                'data' => $review
            ], 201);
        });
    }
}
