<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Services\StaffPerformanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffPerformanceServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function staff_performance_service_exists()
    {
        $service = new StaffPerformanceService();
        $this->assertInstanceOf(StaffPerformanceService::class, $service);
    }

    /** @test */
    public function service_has_calculate_monthly_metrics_method()
    {
        $service = new StaffPerformanceService();
        $staff = Staff::factory()->create();
        
        $this->assertTrue(method_exists($service, 'calculateMonthlyMetrics'));
        $result = $service->calculateMonthlyMetrics($staff);
        $this->assertNotNull($result);
    }

    /** @test */
    public function service_has_get_performance_summary_method()
    {
        $service = new StaffPerformanceService();
        $staff = Staff::factory()->create();
        
        $this->assertTrue(method_exists($service, 'getPerformanceSummary'));
        $result = $service->getPerformanceSummary($staff);
        $this->assertIsArray($result);
    }

    /** @test */
    public function service_has_get_top_performers_method()
    {
        $service = new StaffPerformanceService();
        $salon = \App\Models\Salon::factory()->create();
        
        $this->assertTrue(method_exists($service, 'getTopPerformers'));
        $result = $service->getTopPerformers($salon->id);
        $this->assertTrue(is_array($result) || is_object($result));
    }

    /** @test */
    public function service_has_get_attendance_report_method()
    {
        $service = new StaffPerformanceService();
        $staff = Staff::factory()->create();
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        
        $this->assertTrue(method_exists($service, 'getAttendanceReport'));
        $result = $service->getAttendanceReport($staff, $startDate, $endDate);
        $this->assertTrue(is_array($result) || is_object($result));
    }

    /** @test */
    public function service_has_get_salon_insights_method()
    {
        $service = new StaffPerformanceService();
        $salon = \App\Models\Salon::factory()->create();
        
        $this->assertTrue(method_exists($service, 'getSalonPerformanceInsights'));
        $result = $service->getSalonPerformanceInsights($salon);
        $this->assertIsArray($result);
    }
}
