<?php

namespace Tests\Feature;

use App\Models\Salon;
use App\Models\Staff;
use App\Models\StaffInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwilioWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_logs_response_from_known_staff()
    {
        $salon = Salon::factory()->create();
        $staff = Staff::factory()->create([
            'salon_id' => $salon->id,
            'whatsapp_number' => '+966501234567',
        ]);

        $payload = [
            'From' => 'whatsapp:+966501234567',
            'Body' => 'حاضر',
            'MessageSid' => 'test_sid',
        ];

        $response = $this->postJson('/api/twilio/webhook', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('staff_interactions', [
            'staff_id' => $staff->id,
            'type' => 'response_received',
            'status' => 'success',
            'details' => 'Response: حاضر',
        ]);
    }

    public function test_webhook_ignores_unknown_number()
    {
        $payload = [
            'From' => 'whatsapp:+966509876543',
            'Body' => 'test',
            'MessageSid' => 'test_sid',
        ];

        $response = $this->postJson('/api/twilio/webhook', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('staff_interactions', [
            'type' => 'response_received',
        ]);
    }
}