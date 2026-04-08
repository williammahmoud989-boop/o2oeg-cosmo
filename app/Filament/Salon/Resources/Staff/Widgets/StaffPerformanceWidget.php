<?php

namespace App\Filament\Salon\Resources\Staff\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;

class StaffPerformanceWidget extends StatsOverviewWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $bookingsCount = Booking::where('staff_id', $this->record->id)
            ->where('status', 'completed')
            ->count();

        $totalRevenue = Booking::where('staff_id', $this->record->id)
            ->where('status', 'completed')
            ->sum('total_price');

        $totalCommission = Booking::where('staff_id', $this->record->id)
            ->where('status', 'completed')
            ->sum('commission_amount');

        return [
            Stat::make('إجمالي الحجوزات المكتملة', $bookingsCount)
                ->description('عدد الخدمات التي تم تنفيذها بنجاح')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('إجمالي الإيرادات', number_format($totalRevenue, 2) . ' EGP')
                ->description('إجمالي قيمة الخدمات المنفذة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('إجمالي العمولات المستحقة', number_format($totalCommission, 2) . ' EGP')
                ->description('مستحقات الموظف بناءً على نسبة العمولة')
                ->descriptionIcon('heroicon-m-gift')
                ->color('warning'),
        ];
    }
}

