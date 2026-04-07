<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\StaffInteraction;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class StaffAttendanceReminderService
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.auth_token')
        );
    }

    /**
     * Send morning message to a staff member
     */
    public function sendMorningMessage(Staff $staff): bool
    {
        if (!$staff->attendance_reminder_enabled || !$staff->privacy_consent || !$staff->whatsapp_number) {
            return false;
        }

        try {
            $message = "صباح الخير يا معلمين، توكلنا علي الله";

            $this->twilio->messages->create(
                'whatsapp:' . $staff->whatsapp_number,
                [
                    'from' => 'whatsapp:' . config('services.twilio.whatsapp_from'),
                    'body' => $message,
                ]
            );

            $this->logInteraction($staff, 'message_sent', 'success', $message);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message to staff ' . $staff->id . ': ' . $e->getMessage());
            $this->logInteraction($staff, 'message_sent', 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Check response and call if no reply within 30 minutes
     */
    public function checkResponseAndCall(Staff $staff): bool
    {
        // This would be called by a scheduled job
        // For now, assume we check if response was received
        // In real implementation, use webhooks to track responses

        if (!$staff->attendance_reminder_enabled || !$staff->privacy_consent || !$staff->whatsapp_number) {
            return false;
        }

        try {
            $call = $this->twilio->calls->create(
                $staff->whatsapp_number,
                config('services.twilio.whatsapp_number'),
                [
                    'url' => route('twilio.voice'), // URL for voice message
                ]
            );

            $this->logInteraction($staff, 'call_made', 'success', 'Call SID: ' . $call->sid);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to call staff ' . $staff->id . ': ' . $e->getMessage());
            $this->logInteraction($staff, 'call_made', 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Schedule reminders for all enabled staff
     */
    public function scheduleReminders(): void
    {
        $staffMembers = Staff::where('attendance_reminder_enabled', true)
            ->where('privacy_consent', true)
            ->whereNotNull('whatsapp_number')
            ->whereNotNull('attendance_time')
            ->get();

        foreach ($staffMembers as $staff) {
            // Check if current time matches attendance time
            if ($staff->attendance_time && now()->format('H:i') === \Carbon\Carbon::parse($staff->attendance_time)->format('H:i')) {
                $this->sendMorningMessage($staff);
            }
        }
    }

    /**
     * Log interaction for auditing
     */
    protected function logInteraction(Staff $staff, string $type, string $status, ?string $details = null): void
    {
        StaffInteraction::create([
            'staff_id' => $staff->id,
            'type' => $type,
            'status' => $status,
            'details' => $details,
            'sent_at' => now(),
        ]);

        Log::info("Staff interaction logged: Staff {$staff->id}, Type: {$type}, Status: {$status}");
    }
}