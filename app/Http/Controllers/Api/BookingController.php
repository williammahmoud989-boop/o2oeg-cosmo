<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Resources\BookingResource;
use App\Services\Payment\PaymobService;
use App\Services\Loyalty\LoyaltyService;
use App\Services\Communication\WhatsAppService;
use App\Models\Coupon;
use App\Services\Payment\PricingService;
use App\Services\Marketing\ReferralService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\LoyaltyTransaction;
use App\Models\Salon;
use App\Models\Service;

class BookingController extends Controller
{
    protected $paymob;
    protected $loyalty;
    protected $whatsapp;
    protected $pricing;
    protected $referral;

    public function __construct(PaymobService $paymob, LoyaltyService $loyalty, WhatsAppService $whatsapp, PricingService $pricing, ReferralService $referral)
    {
        $this->paymob = $paymob;
        $this->loyalty = $loyalty;
        $this->whatsapp = $whatsapp;
        $this->pricing = $pricing;
        $this->referral = $referral;
    }

    public function index(Request $request)
    {
        $bookings = $request->user()->bookings()->with(['salon', 'service', 'review'])->orderBy('booking_date', 'desc')->paginate(15);
        return BookingResource::collection($bookings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'salon_id' => 'required|exists:salons,id',
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'total_price' => 'required|numeric',
            'payment_method' => 'required|string|in:card,wallet,cash,vodafone_cash,instapay',
            'payment_receipt' => 'nullable|image|max:2048',
            'referral_code' => 'nullable|string|exists:influencers,referral_code',
        ]);

        $salon = Salon::findOrFail($validated['salon_id']);
        $user = $request->user();
        
        $service = Service::findOrFail($validated['service_id']);
        
        // Calculate price based on service rules (Happy Hour, etc.) and original service price
        $pricingResult = $this->pricing->calculatePrice($salon, $service, $validated['booking_date'], $validated['start_time']);
        $totalPrice = $pricingResult['final_price'];
        $ruleName = $pricingResult['rule_name'];

        $discountAmount = 0;
        $couponId = null;

        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)->where('salon_id', $salon->id)->where('is_active', true)->first();
            if ($coupon && (!$coupon->expires_at || $coupon->expires_at->isFuture())) {
                $discountAmount = ($coupon->type === 'percentage') ? ($totalPrice * $coupon->value / 100) : $coupon->value;
                $couponId = $coupon->id;
                $coupon->increment('used_count');
            }
        }

        // --- Loyalty Redemption Logic Setup ---
        $pointsRedeemed = 0;
        $pointsDiscount = 0;
        if ($request->redeem_points && $request->points_to_redeem > 0) {
            $requestedPoints = (int) $request->points_to_redeem;
            
            // Limit redemption to 50% of the total price (System 1: 1 Point = 1 EGP discount)
            $maxDiscountValue = $totalPrice * 0.5;
            $maxPointsAllowed = (int) floor($maxDiscountValue); // 1 pt = 1 EGP
            
            $pointsToRedeem = min($requestedPoints, $user->loyalty_points, $maxPointsAllowed);
            
            if ($pointsToRedeem > 0) {
                $pointsRedeemed = $pointsToRedeem;
                $pointsDiscount = $pointsToRedeem; // System 1 maps points 1:1 to discount
            }
        }

        $finalPrice = max(0, $totalPrice - ($discountAmount + $pointsDiscount));
        
        $bookingDate = Carbon::parse($validated['booking_date']);
        $dayName = strtolower($bookingDate->format('l'));
        $requiresDeposit = $salon->requires_deposit && 
                           is_array($salon->deposit_days) && 
                           in_array($dayName, $salon->deposit_days);

        $depositAmount = 0;
        $receiptPath = null;

        if ($requiresDeposit) {
            $depositAmount = ($finalPrice * $salon->deposit_percentage) / 100;
        }

        // Handle manual payment proof
        if (in_array($validated['payment_method'], ['vodafone_cash', 'instapay'])) {
            if (!$request->hasFile('payment_receipt')) {
                return response()->json([
                    'message' => 'يجب رفع صورة إيصال التحويل لتأكيد الحجز.',
                    'errors' => ['payment_receipt' => ['The payment receipt field is required for manual payments.']]
                ], 422);
            }
        }

        if ($request->hasFile('payment_receipt')) {
            $receiptPath = $request->file('payment_receipt')->store('bookings/receipts', 'public');
        }

        // Use a database transaction to ensure everything is atomic
        return DB::transaction(function () use ($validated, $user, $salon, $finalPrice, $depositAmount, $receiptPath, $discountAmount, $pricingResult, $pointsRedeemed, $pointsDiscount, $requiresDeposit, $request, $couponId, $ruleName) {
            $booking = $user->bookings()->create([
                'booking_code' => 'BKG-' . strtoupper(uniqid()),
                'salon_id' => $validated['salon_id'],
                'service_id' => $validated['service_id'],
                'booking_date' => $validated['booking_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'total_price' => $finalPrice,
                'deposit_amount' => $depositAmount,
                'payment_receipt' => $receiptPath,
                'payment_method' => $validated['payment_method'] ?? null,
                'discount_amount' => $discountAmount + ($pricingResult['discount_applied'] ?? 0) + ($pointsDiscount ?? 0),
                'coupon_id' => $couponId,
                'points_redeemed' => $pointsRedeemed,
                'status' => 'pending',
                'payment_status' => (isset($validated['payment_method']) && in_array($validated['payment_method'], ['card', 'wallet']) && !$requiresDeposit) ? 'paid' : 'pending',
                'notes' => ($ruleName ? "Applied Global Rule: {$ruleName}. " : "") . ($request->notes ?? '') . (($pointsRedeemed ?? 0) > 0 ? " [Loyalty Redeemed: {$pointsRedeemed} pts]" : ""),
            ]);

            // Execute Loyalty point redemption with real booking ID
            if (isset($pointsRedeemed) && $pointsRedeemed > 0) {
                try {
                    $this->loyalty->redeemPoints($user, $pointsRedeemed, $booking);
                } catch (\Exception $e) {
                    Log::error("Glow Points redemption failed for booking {$booking->id}: " . $e->getMessage());
                    throw $e;
                }
            }

            // Track Influencer Referral
            if ($request->referral_code) {
                $this->referral->trackBooking($booking, $request->referral_code);
            }

            $booking->load(['salon', 'service']);

            // Paymob Integration for Automated Payments
            $paymentUrl = null;
            if (in_array($validated['payment_method'], ['card', 'wallet'])) {
                $authToken = $this->paymob->authenticate();
                if ($authToken) {
                    $orderId = $this->paymob->registerOrder($authToken, $booking);
                    if ($orderId) {
                        $paymentKey = $this->paymob->generatePaymentKey($authToken, $orderId, $booking, $user);
                        if ($paymentKey) {
                            $paymentUrl = $this->paymob->getIframeUrl($paymentKey);
                        }
                    }
                }
            }

            // Send WhatsApp Notification to Salon
            $salonPhone = $salon->whatsapp_number ?? $salon->phone;
            $whatsappUrl = null;

            if ($salonPhone) {
                // Professional Arabic Message
                $msgFormat = "✨ *حجز جديد عبر O2OEG COSMO* ✨\n\n" .
                           "*العميلة:* %s\n" .
                           "*الخدمة:* %s\n" .
                           "*التاريخ:* %s\n" .
                           "*الوقت:* %s\n\n" .
                           "*كود الحجز:* `%s`\n\n" .
                           "يرجى مراجعة لوحة التحكم لتأكيد الموعد.";
                           
                $msg = sprintf($msgFormat, 
                    $user->name, 
                    $booking->service->name_ar ?? $booking->service->name, 
                    $booking->booking_date->format('Y-m-d'), 
                    $booking->start_time, 
                    $booking->booking_code
                );
                
                $this->whatsapp->sendMessage($salonPhone, $msg);
                $whatsappUrl = $this->whatsapp->formatWhatsAppUrl($salonPhone, $msg);
            }

            // Send WhatsApp Notification to Customer
            if ($user->phone) {
                $salonName = $salon->name_ar ?: $salon->name;
                $customerMsg = "🌹 *شكراً لثقتك بـ {$salonName}!* 🌹\n\n" .
                               "تم استلام طلب حجزك بنجاح.\n" .
                               "*الخدمة:* " . ($booking->service->name_ar ?? $booking->service->name) . "\n" .
                               "*الموعد:* " . $booking->booking_date->format('Y-m-d') . " الساعة " . $booking->start_time . "\n" .
                               "*كود الحجز:* `" . $booking->booking_code . "`\n\n";
                
                if ($requiresDeposit) {
                    $customerMsg .= "⚠️ *يرجى إكمال الدفع لتأكيد الحجز:*\n" .
                                    "المبلغ المطلوب: {$depositAmount} EGP\n";
                    
                    if ($paymentUrl) {
                        $customerMsg .= "رابط الدفع: {$paymentUrl}\n";
                    } else {
                        if ($salon->vodafone_cash_number) $customerMsg .= "فودافون كاش: {$salon->vodafone_cash_number}\n";
                        if ($salon->instapay_id) $customerMsg .= "إنستا باي: {$salon->instapay_id}\n";
                    }
                }

                $customerMsg .= "\nنتطلع لرؤيتك قريباً! ✨";
                
                $this->whatsapp->sendMessage($user->phone, $customerMsg);
            }

            if ($receiptPath) {
                Log::info("Manual payment receipt uploaded for booking {$booking->booking_code} path: {$receiptPath}");
            }

            return (new BookingResource($booking))
                ->additional(['meta' => [
                    'requires_deposit' => $requiresDeposit,
                    'deposit_amount' => $depositAmount,
                    'whatsapp_url' => $whatsappUrl,
                    'payment_url' => $paymentUrl,
                    'payment_instructions' => $requiresDeposit ? [
                        'vodafone_cash' => $salon->vodafone_cash_number,
                        'instapay' => $salon->instapay_id,
                    ] : null
                ]]);
        });

    }

    public function show(Request $request, Booking $booking)
    {
        $booking->load(['salon', 'service']);
        return (new BookingResource($booking))
            ->additional(['meta' => [
                'requires_payment' => $booking->deposit_amount > 0 && $booking->payment_status === 'pending'
            ]]);
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking already cancelled'], 422);
        }

        $booking->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $request->reason ?? 'Cancelled by customer',
        ]);

        return new BookingResource($booking->load(['salon', 'service']));
    }

    /**
     * Update booking status (Salon owner / Admin).
     * When completed — awards Glow Points automatically.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $oldStatus = $booking->status;
        $booking->update(['status' => $validated['status']]);

        // Award Glow Points when booking is marked as completed
        if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
            $this->loyalty->awardPoints($booking->user, $booking);
        }

        return new BookingResource($booking->load(['salon', 'service']));
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'salon_id' => 'required|exists:salons,id',
            'service_id' => 'nullable|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'required|integer|min:5',
        ]);

        $salon = \App\Models\Salon::findOrFail($validated['salon_id']);
        $date = Carbon::parse($validated['date']);
        $dayName = strtolower($date->format('l'));

        $workingHours = $salon->working_hours[$dayName] ?? null;

        if (!$workingHours || ($workingHours['closed'] ?? false)) {
            return response()->json(['slots' => [], 'message' => 'Salon is closed on this day'], 200);
        }

        // Calculate estimated price for this date/salon if service_id is provided
        $estimatedPrice = null;
        if ($request->service_id) {
            $service = Service::find($request->service_id);
            if ($service) {
                // Use the PricingService for the requested date (at 12:00 as a pivot time)
                $pricing = $this->pricing->calculatePrice($salon, $service, $validated['date'], '12:00');
                $estimatedPrice = $pricing['final_price'];
            }
        }

        $start = Carbon::parse($validated['date'] . ' ' . $workingHours['open']);
        $end = Carbon::parse($validated['date'] . ' ' . $workingHours['close']);
        
        $duration = $validated['duration'];
        $slots = [];

        $existingBookings = Booking::where('salon_id', $salon->id)
            ->where('booking_date', $validated['date'])
            ->where('status', '!=', 'cancelled')
            ->get();

        $current = $start->copy();
        while ($current->copy()->addMinutes($duration)->lte($end)) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($duration);

            $isAvailable = !$existingBookings->contains(function ($booking) use ($slotStart, $slotEnd) {
                $bookingStart = Carbon::parse($booking->booking_date . ' ' . $booking->start_time);
                $bookingEnd = Carbon::parse($booking->booking_date . ' ' . $booking->end_time);
                
                return $slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart);
            });

            if ($isAvailable && $slotStart->gte(now())) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                ];
            }

            $current->addMinutes(30);
        }

        return response()->json([
            'slots' => $slots,
            'estimated_price' => $estimatedPrice,
            'requires_deposit' => $salon->requires_deposit && in_array($dayName, $salon->deposit_days ?? []),
            'deposit_percentage' => $salon->deposit_percentage ?? 0
        ]);
    }
}
