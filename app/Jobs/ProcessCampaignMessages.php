<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use App\Models\Booking;
use App\Services\Communication\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessCampaignMessages implements ShouldQueue
{
    use Queueable;

    protected MarketingCampaign $campaign;

    /**
     * Create a new job instance.
     */
    public function __construct(MarketingCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        $this->campaign->update(['status' => 'processing']);

        try {
            // Get unique phone numbers of customers who booked at this salon
            $customers = Booking::where('salon_id', $this->campaign->salon_id)
                ->with('user')
                ->get()
                ->pluck('user.phone')
                ->unique()
                ->filter()
                ->values();

            $total = count($customers);
            $this->campaign->update(['total_recipients' => $total]);

            $sent = 0;
            foreach ($customers as $phone) {
                // Refresh status to allow cancellation checking
                $this->campaign->refresh();
                if ($this->campaign->status === 'cancelled') {
                    return;
                }

                $whatsAppService->sendMessage($phone, $this->campaign->message);
                $sent++;
                
                $this->campaign->update(['sent_count' => $sent]);

                // Anti-spam delay (2 seconds per message)
                if ($sent < $total) {
                    sleep(2);
                }
            }

            $this->campaign->update(['status' => 'completed']);

        } catch (\Exception $e) {
            Log::error('Campaign Failed: ' . $e->getMessage());
            $this->campaign->update([
                'status' => 'failed',
                'error_log' => $e->getMessage()
            ]);
        }
    }
}
