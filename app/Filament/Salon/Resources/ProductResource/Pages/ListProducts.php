<?php

namespace App\Filament\Salon\Resources\ProductResource\Pages;

use App\Filament\Salon\Resources\ProductResource;
use Filament\Resources\Pages\ManageRecords;

class ListProducts extends ManageRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
