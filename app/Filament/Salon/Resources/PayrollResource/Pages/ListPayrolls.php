<?php

namespace App\Filament\Salon\Resources\PayrollResource\Pages;

use App\Filament\Salon\Resources\PayrollResource;
use Filament\Resources\Pages\ManageRecords;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

