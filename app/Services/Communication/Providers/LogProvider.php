<?php

namespace App\Services\Communication\Providers;

use App\Services\Communication\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Log;

class LogProvider implements WhatsAppProviderInterface
{
    public function send(string $to, string $message): bool
    {
        Log::info("WhatsApp LOG Provider: Sending to {$to}", [
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ]);
        return true;
    }
}
