<?php

namespace App\Filament\Salon\Resources\ExpenseCategoryResource\Pages;

use App\Filament\Salon\Resources\ExpenseCategoryResource;
use Filament\Resources\Pages\ManageRecords;

class ListExpenseCategories extends ManageRecords
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
