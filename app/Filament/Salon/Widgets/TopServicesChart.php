<?php

namespace App\Filament\Salon\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class TopServicesChart extends ChartWidget
{
    protected ?string $heading = 'الخدمات الأكثر مبيعاً';
    protected string $color = 'info';

    protected function getData(): array
    {
        $salonId = Filament::getTenant()->id;

        $data = Booking::where('salon_id', $salonId)
            ->where('payment_status', 'paid')
            ->select(
                DB::raw('count(*) as count'),
                'service_id'
            )
            ->with('service')
            ->groupBy('service_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'مرات الحجز',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => ['#f43f5e', '#6366f1', '#eab308', '#22c55e', '#0ea5e9'],
                ],
            ],
            'labels' => $data->map(function ($item) {
                return $item->service->name_ar ?? $item->service->name;
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
