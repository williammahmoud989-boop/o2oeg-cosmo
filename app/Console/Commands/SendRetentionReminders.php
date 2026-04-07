<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Booking;
use App\Services\Communication\WhatsAppService;
use Carbon\Carbon;

class SendRetentionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cosmo:send-retention-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends WhatsApp reminders to customers who haven\'t booked in 30 days';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $whatsAppService)
    {
        $this->info('Starting retention reminders process...');

        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Find users whose last completed booking was more than 30 days ago
        // AND who don't have any upcoming bookings
        $usersToRemind = User::where('is_admin', false)
            ->whereHas('bookings', function ($query) use ($thirtyDaysAgo) {
                $query->where('status', 'completed')
                    ->where('booking_date', '<=', $thirtyDaysAgo);
            })
            ->whereDoesntHave('bookings', function ($query) {
                $query->whereIn('status', ['pending', 'confirmed'])
                    ->where('booking_date', '>=', Carbon::today());
            })
            ->with(['lastBooking.salon'])
            ->get();

        $this->info("Found {$usersToRemind->count()} users to remind.");

        foreach ($usersToRemind as $user) {
            $lastBooking = $user->lastBooking;
            if (!$lastBooking || !$lastBooking->salon) {
                continue;
            }

            $salonName = $lastBooking->salon->name_ar ?: $lastBooking->salon->name;
            $customerName = $user->name;

            // Professional, friendly message starting with Salon Name
            $message = "{$salonName} تتمنى لكِ يوماً جميلاً يا {$customerName}! ✨\n\n" .
                       "نتمنى أن تكون خدمتنا الأخيرة قد نالت رضاكِ التام. نحن بانتظار عودتكِ لنعتني بجمالكِ من جديد، ولا تفوتي متابعة عروضنا الحصرية المتاحة الآن على تطبيق O2OEG Cosmo! 💅💄";

            $this->info("Sending to {$user->phone} Reference: {$salonName}");
            
            $whatsAppService->sendMessage($user->phone, $message);
            
            // Avoid rate limiting during scan
            sleep(1);
        }

        $this->info('Retention reminders process completed.');
    }
}
