<?php

namespace App\Services\Communication\Providers;

use App\Services\Communication\WhatsAppProviderInterface;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioProvider implements WhatsAppProviderInterface
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');
        $this->from = config('services.twilio.whatsapp_from');
        $this->client = new Client($sid, $token);
    }

    public function send(string $to, string $message): bool
    {
        try {
            $toFormatted = str_starts_with($to, 'whatsapp:') ? $to : "whatsapp:{$to}";
            $fromFormatted = str_starts_with($this->from, 'whatsapp:') ? $this->from : "whatsapp:{$this->from}";

            $this->client->messages->create($toFormatted, [
                'from' => $fromFormatted,
                'body' => $message,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Twilio WhatsApp Send Failed: ' . $e->getMessage());
            return false;
        }
    }
}
