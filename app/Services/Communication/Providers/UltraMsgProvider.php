<?php

namespace App\Services\Communication\Providers;

use App\Services\Communication\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UltraMsgProvider implements WhatsAppProviderInterface
{
    protected string $instanceId;
    protected string $token;

    public function __construct()
    {
        $this->instanceId = config('services.ultramsg.instance_id');
        $this->token = config('services.ultramsg.token');
    }

    public function send(string $to, string $message): bool
    {
        try {
            // UltraMsg expects international format without '+' or 'whatsapp:' prefix
            $to = preg_replace('/\D/', '', $to);
            
            $response = Http::post("https://api.ultramsg.com/{$this->instanceId}/messages/chat", [
                'token' => $this->token,
                'to' => $to,
                'body' => $message,
                'priority' => 10,
                'referenceId' => '',
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('UltraMsg Send Failed: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('UltraMsg Send Error: ' . $e->getMessage());
            return false;
        }
    }
}
