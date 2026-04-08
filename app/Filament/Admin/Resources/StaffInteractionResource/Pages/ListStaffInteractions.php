<?php

namespace App\Filament\Admin\Resources\StaffInteractionResource\Pages;

use App\Filament\Admin\Resources\StaffInteractionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffInteractions extends ListRecords
{
    protected static string $resource = StaffInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}



