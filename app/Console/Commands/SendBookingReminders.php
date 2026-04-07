<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Services\Communication\WhatsAppService;
use Carbon\Carbon;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp reminders to customers 2 hours before their booking';

    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        parent::__construct();
        $this->whatsapp = $whatsapp;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $twoHoursFromNow = $now->copy()->addHours(2);

        // Find pending or confirmed bookings starting in approx 2 hours
        $bookings = Booking::where('status', '!=', 'cancelled')
            ->whereDate('booking_date', $now->toDateString())
            ->where('start_time', '>=', $twoHoursFromNow->format('H:i'))
            ->where('start_time', '<=', $twoHoursFromNow->copy()->addMinutes(30)->format('H:i'))
            ->where('reminder_sent', false)
            ->with(['user', 'salon', 'service'])
            ->get();

        $this->info("Found " . $bookings->count() . " bookings to remind.");

        foreach ($bookings as $booking) {
            if (!$booking->user || !$booking->user->phone) continue;

            $salonName = $booking->salon->name_ar ?: $booking->salon->name;
            $serviceName = $booking->service->name_ar ?: $booking->service->name;

            $message = "🔔 *تذكير بموعدك المنتظر!* 🔔\n\n" .
                       "عزيزتي، نود تذكيرك بموعدك القادم اليوم في *{$salonName}*.\n\n" .
                       "📍 *الخدمة:* {$serviceName}\n" .
                       "⏰ *الوقت:* {$booking->start_time}\n" .
                       "🏠 *العنوان:* {$booking->salon->address}\n\n" .
                       "نحن بانتظارك لتجربة جمال غامرة! ✨";

            $success = $this->whatsapp->sendMessage($booking->user->phone, $message);

            if ($success) {
                $booking->update(['reminder_sent' => true]);
                $this->info("Reminder sent to {$booking->user->phone}");
            } else {
                $this->error("Failed to send reminder to {$booking->user->phone}");
            }
        }
    }
}
