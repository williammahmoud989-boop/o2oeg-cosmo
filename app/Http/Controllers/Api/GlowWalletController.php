<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Loyalty\LoyaltyService;

class GlowWalletController extends Controller
{
    protected $loyalty;

    public function __construct(LoyaltyService $loyalty)
    {
        $this->loyalty = $loyalty;
    }

    /**
     * Get the authenticated user's Glow Wallet summary.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $wallet = $this->loyalty->getWallet($user);

        return response()->json([
            'data' => $wallet,
        ]);
    }
}
