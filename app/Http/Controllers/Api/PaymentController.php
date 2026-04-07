<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Services\Loyalty\LoyaltyService;
use App\Services\Payment\PaymobService;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $loyalty;
    protected $paymob;

    public function __construct(LoyaltyService $loyalty, PaymobService $paymob)
    {
        $this->loyalty = $loyalty;
        $this->paymob = $paymob;
    }

    public function process(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|string',
        ]);

        $booking = Booking::with('salon', 'user')->findOrFail($request->booking_id);

        if ($request->payment_method === 'card') {
            $this->paymob->initializeForSalon($booking->salon);

            $token = $this->paymob->authenticate();
            if (!$token) return response()->json(['message' => 'Payment gateway error'], 500);

            $orderId = $this->paymob->registerOrder($token, $booking);
            if (!$orderId) return response()->json(['message' => 'Order registration error'], 500);

            $paymentToken = $this->paymob->generatePaymentKey($token, $orderId, $booking, $booking->user);

            return response()->json([
                'payment_url' => $this->paymob->getIframeUrl($paymentToken),
                'order_id' => $orderId
            ]);
        }

        $booking->update([
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Payment instruction sent',
            'booking' => $booking
        ]);
    }

    public function paymobCallback(Request $request)
    {
        $success = $request->success === 'true';
        $bookingId = $request->obj['order']['merchant_order_id'] ?? null;
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

        if ($success) {
            return \redirect("{$frontendUrl}/booking/success?booking_id={$bookingId}");
        }

        return \redirect("{$frontendUrl}/booking/failed?booking_id={$bookingId}");
    }

    public function paymobWebhook(Request $request)
    {
        $data = $request->all();
        $hmac = $request->header('hmac');

        $obj = $data['obj'] ?? [];
        $success = $obj['success'] ?? false;
        $bookingCode = $obj['order']['merchant_order_id'] ?? null;
        $transactionId = $obj['id'] ?? null;

        if ($success && $bookingCode) {
            $booking = Booking::with('salon')->where('booking_code', $bookingCode)->first();

            if ($booking) {
                $this->paymob->initializeForSalon($booking->salon);
                if (!$this->paymob->verifyHmac($obj, $hmac)) {
                    Log::warning("Paymob HMAC verification failed for booking: {$bookingCode}");
                    return response()->json(['error' => 'Unauthorized'], 401);
                }

                if ($booking->payment_status !== 'paid') {
                    $booking->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                        'payment_id' => $transactionId,
                    ]);

                    $this->loyalty->awardPoints($booking->user, $booking);
                }
            }
        }

        return \response()->json(['status' => 'ok']);
    }
}
