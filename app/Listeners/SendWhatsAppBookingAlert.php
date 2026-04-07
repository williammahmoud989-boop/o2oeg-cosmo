<?php

namespace App\Listeners;

use App\Events\BookingSaved;
use App\Services\WhatsAppNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWhatsAppBookingAlert implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        protected WhatsAppNotificationService $whatsapp
    ) {}

    /**
     * Handle the event.
     */
    public function handle(BookingSaved $event): void
    {
        // Only trigger this for newly confirmed bookings or upcoming ones.
        // If we want to be safe and only send on creation, we would check the booking status.
        if ($event->booking->isConfirmed()) {
            $this->whatsapp->sendBookingConfirmation($event->booking);
        }
    }
}
