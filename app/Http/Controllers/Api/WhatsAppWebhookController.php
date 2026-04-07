<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Services\Communication\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsAppWebhookController extends Controller
{
    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Handle incoming webhooks from the WhatsApp provider (e.g., UltraMsg).
     */
    public function handle(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('WhatsApp Webhook Received:', $request->all());

        // Example for UltraMsg: 
        // { "event_type": "message_received", "data": { "body": "1", "from": "2010xxxxxxxx", ... } }
        
        $data = $request->input('data');
        if (!$data || !isset($data['body'])) {
            return response()->json(['status' => 'ignored']);
        }

        $messageBody = trim($data['body']);
        $senderPhone = $data['from'];

        // Logic: If user replies with '1', confirm their latest pending booking
        if ($messageBody === '1') {
            return $this->confirmBookingByPhone($senderPhone);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function confirmBookingByPhone($phone)
    {
        // Find the latest pending booking for this phone number
        $booking = Booking::whereHas('user', function ($query) use ($phone) {
            $query->where('phone', $phone);
        })
        ->where('status', 'pending')
        ->latest()
        ->first();

        if ($booking) {
            $booking->update([
                'status' => 'confirmed',
                'confirmed_at' => Carbon::now(),
            ]);

            $salonName = $booking->salon->name_ar ?: $booking->salon->name;
            $this->whatsapp->sendMessage($phone, "✅ تم تأكيد حجزك في *{$salonName}* بنجاح. نحن بانتظارك! ✨");

            return response()->json(['status' => 'booking_confirmed']);
        }

        return response()->json(['status' => 'no_pending_booking']);
    }
}
