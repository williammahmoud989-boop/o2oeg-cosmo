<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Carbon;

class OfferController extends Controller
{
    /**
     * Return active offers.
     * Currently returns an empty array so the frontend shows the "coming soon" state.
     * When an Offer model/table is added, replace this with real data.
     */
    public function index(Request $request)
    {
        // Check if Offer model exists, otherwise return empty
        if (!\class_exists(\App\Models\Offer::class)) {
            return Response::json(['data' => [], 'meta' => ['total' => 0]]);
        }

        $offers = Offer::with([
                'salon:id,name,name_ar,city,rating,cover_image',
                'service:id,name,name_ar,price,duration_minutes',
            ])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return Response::json([
            'data' => $offers->map(function ($offer) {
                return [
                    'id'                  => $offer->id,
                    'title'               => $offer->title ?? null,
                    'title_ar'            => $offer->title_ar ?? null,
                    'description'         => $offer->description ?? null,
                    'description_ar'      => $offer->description_ar ?? null,
                    'discount_percentage' => $offer->discount_percentage ?? null,
                    'original_price'      => $offer->original_price ?? null,
                    'discounted_price'    => $offer->discounted_price ?? null,
                    'expires_at'          => $offer->expires_at?->toDateString(),
                    'salon'               => $offer->salon ? [
                        'id'          => $offer->salon->id,
                        'name'        => $offer->salon->name,
                        'name_ar'     => $offer->salon->name_ar,
                        'city'        => $offer->salon->city,
                        'rating'      => $offer->salon->rating,
                        'cover_image' => $offer->salon->cover_image,
                    ] : null,
                    'service'             => $offer->service ? [
                        'name'             => $offer->service->name,
                        'name_ar'          => $offer->service->name_ar,
                        'price'            => $offer->service->price,
                        'duration_minutes' => $offer->service->duration_minutes,
                    ] : null,
                ];
            }),
            'meta' => [
                'total'        => $offers->total(),
                'current_page' => $offers->currentPage(),
                'last_page'    => $offers->lastPage(),
            ],
        ]);
    }
}
