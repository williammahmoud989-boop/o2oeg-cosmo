<?php

namespace App\Filament\Salon\Resources\SupplierResource\Pages;

use App\Filament\Salon\Resources\SupplierResource;
use Filament\Resources\Pages\ManageRecords;

class ListSuppliers extends ManageRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}

