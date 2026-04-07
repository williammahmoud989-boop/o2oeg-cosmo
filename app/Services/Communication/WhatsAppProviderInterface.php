<?php

namespace App\Services\Communication;

interface WhatsAppProviderInterface
{
    /**
     * Send a WhatsApp message.
     *
     * @param string $to The phone number (e.g., +2010xxxxxxxx)
     * @param string $message The content of the message
     * @return bool
     */
    public function send(string $to, string $message): bool;
}
