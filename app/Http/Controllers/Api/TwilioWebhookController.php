<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaffInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwilioWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log the incoming webhook for debugging
        Log::info('Twilio Webhook Received', $request->all());

        // Handle different types of messages
        $from = $request->input('From'); // e.g., whatsapp:+966501234567
        $body = $request->input('Body');
        $messageSid = $request->input('MessageSid');

        // Extract phone number from whatsapp: prefix
        $phoneNumber = str_replace('whatsapp:', '', $from);

        // Find staff by whatsapp_number
        $staff = \App\Models\Staff::where('whatsapp_number', $phoneNumber)->first();

        if ($staff) {
            // Log the response
            StaffInteraction::create([
                'staff_id' => $staff->id,
                'type' => 'response_received',
                'status' => 'success',
                'details' => "Response: {$body}",
                'sent_at' => now(),
            ]);

            Log::info("Response received from staff {$staff->id}: {$body}");
        } else {
            Log::warning("Received response from unknown number: {$phoneNumber}");
        }

        // Return TwiML response (empty for WhatsApp)
        return response('<Response></Response>', 200, ['Content-Type' => 'text/xml']);
    }
}