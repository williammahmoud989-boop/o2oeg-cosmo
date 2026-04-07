<?php

namespace App\Filament\Salon\Resources\ProductCategoryResource\Pages;

use App\Filament\Salon\Resources\ProductCategoryResource;
use Filament\Resources\Pages\ManageRecords;

class ListProductCategories extends ManageRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
