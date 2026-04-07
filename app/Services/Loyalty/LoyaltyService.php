<?php

namespace App\Services\Loyalty;

use App\Services\Communication\WhatsAppService;
use App\Models\LoyaltyTransaction;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    const MAX_REDEMPTION_PERCENT = 0.5;

    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Award Glow Points to a user upon booking completion.
     * Rate depends on current loyalty tier.
     */
    public function awardPoints(User $user, Booking $booking): void
    {
        if (!$user) return;

        // Ensure points aren't already awarded for this specific booking
        if ($booking->points_earned) return;

        $tiers = User::getTiers();
        $tier = $tiers[$user->loyalty_tier] ?? $tiers[User::TIER_BASIC_GLOW];
        $pointsToAward = (int) round(($booking->total_price ?? 0) * $tier['rate']);

        DB::transaction(function () use ($user, $booking, $pointsToAward, $tier) {
            if ($pointsToAward > 0) {
                LoyaltyTransaction::create([
                    'user_id'    => $user->id,
                    'booking_id' => $booking->id,
                    'points'     => $pointsToAward,
                    'type'       => 'earned',
                    'reason'     => "✨ Glow Points earned for booking #{$booking->booking_code}",
                ]);

                $user->increment('loyalty_points', $pointsToAward);
                $user->increment('loyalty_points_total', $pointsToAward);
                
                // Track points in booking table
                $booking->update(['points_earned' => $pointsToAward]);

                // Re-fresh user to get updated totals before upgrading
                $user->refresh();
                $this->upgradeTierIfEligible($user);

                // Notify User via WhatsApp
                if ($user->phone) {
                    $this->whatsapp->sendMessage(
                        $user->phone,
                        "✨ Glow Up! حصلتي على {$pointsToAward} Glow Points من حجزك الأخير 💫\n" .
                        "رصيدك الحالي: {$user->loyalty_points} Glows 🔮\n" .
                        "مستواك: {$tier['name_ar']} 🌟"
                    );
                }
            }

            // Referral bonus for BOTH parties on the FIRST completed booking
            if ($user->referredBy && $user->bookings()->where('status', 'completed')->count() === 1) {
                $this->awardReferralBonus($user->referredBy, $user);
            }
        });
    }

    /**
     * Award points to both referrer and referee when first booking is completed.
     */
    public function awardReferralBonus(User $referrer, User $referredUser): void
    {
        $bonusPoints = 50;

        // 1. Award Referrer
        LoyaltyTransaction::create([
            'user_id' => $referrer->id,
            'points'  => $bonusPoints,
            'type'    => 'earned',
            'reason'  => "🎁 Glow Bonus for inviting {$referredUser->name}",
        ]);
        $referrer->increment('loyalty_points', $bonusPoints);

        // 2. Award Referred User (Referee)
        LoyaltyTransaction::create([
            'user_id' => $referredUser->id,
            'points'  => $bonusPoints,
            'type'    => 'earned',
            'reason'  => "✨ Welcome Glow Bonus! (Referred by {$referrer->name})",
        ]);
        $referredUser->increment('loyalty_points', $bonusPoints);

        // Notify Referrer
        if ($referrer->phone) {
            $this->whatsapp->sendMessage(
                $referrer->phone,
                "🎁 Glow Bonus! صديقتك/صديقك ({$referredUser->name}) أكمل حجزه الأول 🎉\n" .
                "حصلتي على {$bonusPoints} Glow Points مجاناً! 💫"
            );
        }

        // Notify Referred User
        if ($referredUser->phone) {
            $this->whatsapp->sendMessage(
                $referredUser->phone,
                "✨ Welcome Glow! بمناسبة أول حجز لكي، حصلتي على {$bonusPoints} نقطة هدية لأنك انضممتِ إلينا عبر دعوة صديقة! 💖"
            );
        }
    }

    /**
     * Redeem Glow Points as a discount on a booking (1 Glow = 1 EGP).
     * Max 50% of total booking value.
     */
    public function redeemPoints(User $user, int $pointsRequested, Booking $booking): float
    {
        $maxDiscount = ($booking->total_amount ?? $booking->total_price ?? 0) * self::MAX_REDEMPTION_PERCENT;
        $actualPoints = (int) min($pointsRequested, $maxDiscount, $user->loyalty_points);

        if ($actualPoints <= 0) {
            return 0.0;
        }

        return DB::transaction(function () use ($user, $booking, $actualPoints) {
            LoyaltyTransaction::create([
                'user_id'    => $user->id,
                'booking_id' => $booking->id,
                'points'     => -$actualPoints,
                'type'       => 'spent',
                'reason'     => "💸 Redeemed for booking #{$booking->booking_code}",
            ]);

            $user->decrement('loyalty_points', $actualPoints);

            if ($user->phone) {
                $this->whatsapp->sendMessage(
                    $user->phone,
                    "💸 استخدمتي {$actualPoints} Glow Points كخصم على حجزك 🛍️\n" .
                    "رصيدك المتبقي: {$user->loyalty_points} Glows 🔮"
                );
            }

            return (float) $actualPoints;
        });
    }

    /**
     * Upgrade the user's loyalty tier based on total earned points.
     */
    private function upgradeTierIfEligible(User $user): void
    {
        $tiers = User::getTiers();
        $newTier = User::TIER_BASIC_GLOW;

        foreach ($tiers as $tierKey => $tierData) {
            if ($user->loyalty_points_total >= $tierData['threshold']) {
                $newTier = $tierKey;
            }
        }

        if ($user->loyalty_tier !== $newTier) {
            $user->update(['loyalty_tier' => $newTier]);

            if ($user->phone) {
                $tierName = $tiers[$newTier]['name_ar'];
                $this->whatsapp->sendMessage(
                    $user->phone,
                    "🚀 Glow Up Level! انتقلتي لمستوى {$tierName} 🎊\n" .
                    "هتكسبي نقاط أكتر على كل حجز جاي! ✨"
                );
            }
        }
    }

    /**
     * Get full Glow Wallet summary for a user.
     */
    public function getWallet(User $user): array
    {
        $tiers = User::getTiers();
        $currentTier = $tiers[$user->loyalty_tier] ?? $tiers[User::TIER_BASIC_GLOW];
        $tierKeys = array_keys($tiers);
        $currentIndex = array_search($user->loyalty_tier, $tierKeys);
        $nextTierKey = $tierKeys[$currentIndex + 1] ?? null;
        $nextTier = $nextTierKey ? $tiers[$nextTierKey] : null;

        $progress = 100;
        if ($nextTier) {
            $progress = min(100, (int) round(($user->loyalty_points_total / $nextTier['threshold']) * 100));
        }

        return [
            'balance'             => $user->loyalty_points,
            'total_earned'        => $user->loyalty_points_total,
            'tier'                => $user->loyalty_tier,
            'tier_name'           => $currentTier['name_ar'],
            'earn_rate'           => $currentTier['rate'],
            'next_tier_name'      => $nextTier ? $nextTier['name_ar'] : null,
            'next_tier_threshold' => $nextTier ? $nextTier['threshold'] : null,
            'progress_to_next'    => $progress,
            'recent_transactions' => $user->loyaltyTransactions()
                ->latest()
                ->take(10)
                ->get(),
        ];
    }
}
