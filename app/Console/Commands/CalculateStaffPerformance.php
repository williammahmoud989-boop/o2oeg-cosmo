<?php

namespace App\Console\Commands;

use App\Models\Staff;
use App\Services\StaffPerformanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateStaffPerformance extends Command
{
    protected $signature = 'staff:calculate-performance {--month=} {--year=} {--staff-id=}';

    protected $description = 'Calculate staff performance metrics for a specific month';

    public function handle()
    {
        $performanceService = app(StaffPerformanceService::class);

        $month = $this->option('month') ?? Carbon::now()->month;
        $year = $this->option('year') ?? Carbon::now()->year;

        if ($this->option('staff-id')) {
            $staff = Staff::find($this->option('staff-id'));
            if (!$staff) {
                $this->error("Staff not found");
                return;
            }
            $staffList = [$staff];
        } else {
            $staffList = Staff::where('is_active', true)->get();
        }

        foreach ($staffList as $staff) {
            try {
                $metric = $performanceService->calculateMonthlyMetrics($staff, $month, $year);
                $this->info("Calculated performance for {$staff->name} - Score: {$metric->performance_score}");
            } catch (\Exception $e) {
                $this->error("Error calculating performance for {$staff->name}: {$e->getMessage()}");
            }
        }

        $this->info('Staff performance calculation completed!');
    }
}
