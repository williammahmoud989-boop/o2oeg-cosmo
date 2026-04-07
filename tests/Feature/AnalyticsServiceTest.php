<?php

namespace Tests\Feature;

use App\Models\AnalyticsMetric;
use App\Models\Booking;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    private AnalyticsService $analyticsService;
    private Salon $salon;
    private Service $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->analyticsService = app(AnalyticsService::class);
        
        $uniqueId = uniqid();
        $this->salon = Salon::create([
            'name' => 'Test Salon ' . $uniqueId,
            'slug' => 'test-salon-' . $uniqueId,
            'user_id' => User::factory()->create()->id,
            'status' => 'active',
            'is_featured' => false,
            'subdomain' => 'test-' . $uniqueId,
            'commission_rate' => 10,
        ]);

        $this->user = User::factory()->create();

        $this->service = Service::create([
            'salon_id' => $this->salon->id,
            'name' => 'Test Service',
            'price' => 200,
            'duration_minutes' => 60,
            'is_active' => true,
        ]);
    }

    public function test_calculate_daily_metrics()
    {
        $date = Carbon::now()->toDateString();

        // Create some bookings
        Booking::create([
            'salon_id' => $this->salon->id,
            'user_id' => $this->user->id,
            'service_id' => $this->service->id,
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'total_price' => 200,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        Booking::create([
            'salon_id' => $this->salon->id,
            'user_id' => User::factory()->create()->id,
            'service_id' => $this->service->id,
            'booking_date' => $date,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'total_price' => 200,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        Booking::create([
            'salon_id' => $this->salon->id,
            'user_id' => User::factory()->create()->id,
            'service_id' => $this->service->id,
            'booking_date' => $date,
            'start_time' => '12:00',
            'end_time' => '13:00',
            'total_price' => 200,
            'status' => 'cancelled',
            'payment_status' => 'refunded',
        ]);

        $metric = $this->analyticsService->calculateDailyMetrics($this->salon, $date);

        $this->assertNotNull($metric);
        $this->assertEquals(3, $metric->total_bookings);
        $this->assertEquals(2, $metric->completed_bookings);
        $this->assertEquals(1, $metric->cancelled_bookings);
        $this->assertEquals(400, $metric->total_revenue);
        $this->assertEquals(40, $metric->commission_charged); // 10% of 400
        $this->assertEquals(360, $metric->net_revenue);
        $this->assertEquals(3, $metric->unique_customers); // 3 unique customers
        $this->assertEquals(200, $metric->average_booking_value); // 400 / 2
    }

    public function test_get_dashboard_summary()
    {
        // Create bookings for the last 30 days
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);

        for ($i = 0; $i < 5; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();
            
            Booking::create([
                'salon_id' => $this->salon->id,
                'user_id' => User::factory()->create()->id,
                'service_id' => $this->service->id,
                'booking_date' => $date,
                'start_time' => '10:00',
                'end_time' => '11:00',
                'total_price' => 200,
                'status' => 'completed',
                'payment_status' => 'paid',
            ]);

            $this->analyticsService->calculateDailyMetrics($this->salon, $date);
        }

        $summary = $this->analyticsService->getDashboardSummary($this->salon, 30);

        $this->assertNotNull($summary['current_period']);
        $this->assertGreaterThan(0, $summary['current_period']?->total_bookings);
        $this->assertIsNumeric($summary['revenue_growth']);
    }

    public function test_get_top_services_by_revenue()
    {
        $date = Carbon::now()->toDateString();

        // Create multiple bookings for the service
        for ($i = 0; $i < 5; $i++) {
            Booking::create([
                'salon_id' => $this->salon->id,
                'user_id' => User::factory()->create()->id,
                'service_id' => $this->service->id,
                'booking_date' => $date,
                'start_time' => '10:00',
                'end_time' => '11:00',
                'total_price' => 200,
                'status' => 'completed',
                'payment_status' => 'paid',
            ]);
        }

        $topServices = $this->analyticsService->getTopServicesByRevenue($this->salon, 30);

        $this->assertGreaterThan(0, $topServices->count());
        $this->assertEquals($this->service->id, $topServices->first()->service_id);
    }

    public function test_get_retention_rate()
    {
        $now = Carbon::now();
        $pastDate = $now->copy()->subDays(40)->toDateString();
        $recentDate = $now->copy()->subDays(5)->toDateString();

        // Create a booking in the past (before the 30-day period)
        Booking::create([
            'salon_id' => $this->salon->id,
            'user_id' => $this->user->id,
            'service_id' => $this->service->id,
            'booking_date' => $pastDate,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'total_price' => 200,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        // Create a recent booking from the same customer
        Booking::create([
            'salon_id' => $this->salon->id,
            'user_id' => $this->user->id,
            'service_id' => $this->service->id,
            'booking_date' => $recentDate,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'total_price' => 200,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        // Create a new customer booking
        Booking::create([
            'salon_id' => $this->salon->id,
            'user_id' => User::factory()->create()->id,
            'service_id' => $this->service->id,
            'booking_date' => $recentDate,
            'start_time' => '12:00',
            'end_time' => '13:00',
            'total_price' => 200,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $retentionRate = $this->analyticsService->getRetentionRate($this->salon, 30);

        $this->assertGreaterThanOrEqual(0, $retentionRate);
        $this->assertLessThanOrEqual(100, $retentionRate);
    }
}
