<?php

namespace App\Filament\Admin\Resources\StaffInteractionResource\Pages;

use App\Filament\Admin\Resources\StaffInteractionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffInteraction extends ViewRecord
{
    protected static string $resource = StaffInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}



