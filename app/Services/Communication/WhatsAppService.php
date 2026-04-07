<?php

namespace App\Services\Communication;

use Illuminate\Support\Facades\Log;
use App\Services\Communication\WhatsAppProviderInterface;

class WhatsAppService
{
    protected WhatsAppProviderInterface $provider;

    public function __construct(WhatsAppProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Sends a WhatsApp message using the configured provider.
     *
     * @param string $to The phone number (e.g., +2010xxxxxxxx)
     * @param string $message The content of the message
     * @return bool
     */
    public function sendMessage(string $to, string $message): bool
    {
        return $this->provider->send($to, $message);
    }

    /**
     * Formats a direct WhatsApp link for client-side redirection.
     *
     * @param string $phone
     * @param string $message
     * @return string
     */
    public function formatWhatsAppUrl(string $phone, string $message): string
    {
        // Clean phone number (remove non-digits)
        $phone = preg_replace('/\D/', '', $phone);
        
        // Ensure Egyptian country code if needed (simple check)
        if (strlen($phone) === 11 && str_starts_with($phone, '01')) {
            $phone = '2' . $phone;
        }

        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }
}
