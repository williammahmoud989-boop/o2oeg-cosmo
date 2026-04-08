<?php

namespace App\Filament\Salon\Resources\UserPackageResource\Pages;

use App\Filament\Salon\Resources\UserPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserPackages extends ListRecords
{
    protected static string $resource = UserPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

