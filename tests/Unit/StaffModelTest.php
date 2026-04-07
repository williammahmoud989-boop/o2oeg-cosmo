<?php

namespace Tests\Unit;

use App\Models\Staff;
use App\Models\Salon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_has_new_attendance_fields()
    {
        $salon = Salon::factory()->create();

        $staff = Staff::create([
            'salon_id' => $salon->id,
            'name' => 'Test Staff',
            'specialization' => 'Hair Stylist',
            'base_salary' => 5000.00,
            'commission_rate' => 10.00,
            'is_active' => true,
            'attendance_reminder_enabled' => true,
            'attendance_time' => '08:00:00',
            'whatsapp_number' => '+966501234567',
            'privacy_consent' => true,
            'consent_given_at' => now(),
        ]);

        $this->assertTrue($staff->attendance_reminder_enabled);
        $this->assertEquals('08:00:00', $staff->attendance_time->format('H:i:s'));
        $this->assertEquals('+966501234567', $staff->whatsapp_number);
        $this->assertTrue($staff->privacy_consent);
        $this->assertNotNull($staff->consent_given_at);
    }

    public function test_staff_defaults_for_new_fields()
    {
        $salon = Salon::factory()->create();

        $staff = Staff::create([
            'salon_id' => $salon->id,
            'name' => 'Test Staff 2',
            'specialization' => 'Nail Technician',
            'base_salary' => 4000.00,
            'commission_rate' => 8.00,
            'is_active' => true,
        ]);

        $this->assertFalse($staff->attendance_reminder_enabled);
        $this->assertNull($staff->attendance_time);
        $this->assertNull($staff->whatsapp_number);
        $this->assertFalse($staff->privacy_consent);
        $this->assertNull($staff->consent_given_at);
    }
}