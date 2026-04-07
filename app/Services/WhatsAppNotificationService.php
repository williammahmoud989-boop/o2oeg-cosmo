<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;

class WhatsAppNotificationService
{
    protected Client $twilio;
    protected string $fromNumber;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');
        $this->fromNumber = config('services.twilio.whatsapp_from');
        
        $this->twilio = new Client($sid, $token);
    }

    /**
     * Send a WhatsApp message to a given number.
     * Number must include country code, e.g., +2010...
     */
    public function sendMessage(string $to, string $message): bool
    {
        try {
            // Twilio requires WhatsApp numbers to be prefixed with 'whatsapp:'
            $toFormatted = str_starts_with($to, 'whatsapp:') ? $to : "whatsapp:{$to}";
            $fromFormatted = str_starts_with($this->fromNumber, 'whatsapp:') ? $this->fromNumber : "whatsapp:{$this->fromNumber}";

            $this->twilio->messages->create($toFormatted, [
                'from' => $fromFormatted,
                'body' => $message,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp Notification Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send booking confirmation interactive message.
     */
    public function sendBookingConfirmation(Booking $booking): bool
    {
        // Skip if customer has no phone
        if (!$booking->user->phone) {
            return false;
        }

        $salonName = $booking->salon->name_ar ?? $booking->salon->name;
        $serviceName = $booking->service->name_ar ?? $booking->service->name;
        $date = $booking->booking_date->format('Y-m-d');
        $time = $booking->start_time;
        
        // This simulates a Twilio approved template structure
        $message = "✨ مرحباً {$booking->user->first_name}!\n\n";
        $message .= "تم تأكيد حجزك بنجاح في صالون *{$salonName}*.\n\n";
        $message .= "الخدمة: {$serviceName}\n";
        $message .= "التاريخ: {$date}\n";
        $message .= "الوقت: {$time}\n\n";
        $message .= "📍 موقع الصالون:\nhttps://maps.google.com/?q={$booking->salon->latitude},{$booking->salon->longitude}\n\n";
        $message .= "نشكرك على استخدام O2OEG Cosmo! 💅";

        return $this->sendMessage($booking->user->phone, $message);
    }
}
