<?php

namespace App\Filament\Salon\Resources\Staff\Pages;

use App\Filament\Salon\Resources\Staff\StaffResource;
use App\Filament\Salon\Resources\Staff\Widgets\StaffPerformanceWidget;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StaffPerformanceWidget::class,
        ];
    }
}

