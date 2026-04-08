<?php

namespace App\Filament\Salon\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Facades\Filament;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $salonId = Filament::getTenant()->id;

        $totalRevenue = \App\Models\Booking::where('salon_id', $salonId)
            ->where('payment_status', 'paid')
            ->sum('total_price');

        $pendingBookings = Booking::where('salon_id', $salonId)
            ->where('status', 'pending')
            ->count();

        $receiptsToVerify = Booking::where('salon_id', $salonId)
            ->whereNotNull('payment_receipt')
            ->where('payment_status', 'pending')
            ->count();

        $activeServices = \App\Models\Service::where('salon_id', $salonId)
            ->where('is_active', true)
            ->count();

        $avgRating = Filament::getTenant()->rating ?? 0.0;

        return [
            Stat::make('إجمالي الأرباح', number_format($totalRevenue, 2) . ' ج.م')
                ->description('الأرباح المدفوعة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('التقييم العام', number_format($avgRating, 1) . ' / 5')
                ->description('رضا العملاء')
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),
            Stat::make('تحويلات بانتظار التأكيد', $receiptsToVerify)
                ->description('إيصالات تحتاج مراجعتك')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($receiptsToVerify > 0 ? 'danger' : 'gray'),
            Stat::make('حجوزات بانتظار التأكيد', $pendingBookings)
                ->description('حجوزات جديدة')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }
}

