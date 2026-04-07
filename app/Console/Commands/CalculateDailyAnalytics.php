<?php

namespace App\Console\Commands;

use App\Models\Salon;
use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class CalculateDailyAnalytics extends Command
{
    protected $signature = 'analytics:calculate-daily {--salon-id=}';

    protected $description = 'Calculate daily analytics metrics for salons';

    public function handle()
    {
        $analyticsService = app(AnalyticsService::class);

        if ($this->option('salon-id')) {
            $salon = Salon::find($this->option('salon-id'));
            if (!$salon) {
                $this->error("Salon not found");
                return;
            }
            $salons = [$salon];
        } else {
            $salons = Salon::where('status', 'active')->get();
        }

        foreach ($salons as $salon) {
            try {
                $metric = $analyticsService->calculateDailyMetrics($salon);
                $this->info("Calculated metrics for {$salon->name} on {$metric->date}");
            } catch (\Exception $e) {
                $this->error("Error calculating metrics for {$salon->name}: {$e->getMessage()}");
            }
        }

        $this->info('Daily analytics calculation completed!');
    }
}
