<?php

namespace App\Filament\Salon\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'تحليل الأرباح الشهرية';
    protected string $color = 'success';

    protected function getData(): array
    {
        $salonId = Filament::getTenant()->id;
        $currentYear = \now()->year;

        $results = Booking::where('salon_id', $salonId)
            ->where('payment_status', 'paid')
            ->whereYear('booking_date', $currentYear)
            ->select(
                DB::raw("strftime('%m', booking_date) as month"),
                DB::raw('SUM(total_price) as sum')
            )
            ->groupBy('month')
            ->pluck('sum', 'month')
            ->toArray();

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthKey = str_pad($m, 2, '0', STR_PAD_LEFT);
            $data[] = (float)($results[$monthKey] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'الأرباح (ج.م)',
                    'data' => $data,
                    'fill' => 'start',
                    'tension' => 0.4,
                ],
            ],
            'labels' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
