<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Loyalty\LoyaltyService;

use Illuminate\Support\Facades\Response;

class LoyaltyController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {}

    /**
     * Get user loyalty balance and rank.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $wallet = $this->loyaltyService->getWallet($user);

        return Response::json([
            'points' => $user->loyalty_points,
            'tier' => $wallet,
            'transactions_count' => $user->loyaltyTransactions()->count(),
        ]);
    }

    /**
     * Get user loyalty transactions history.
     */
    public function transactions(Request $request)
    {
        $transactions = $request->user()
            ->loyaltyTransactions()
            ->with('booking.salon')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Response::json($transactions);
    }
}
