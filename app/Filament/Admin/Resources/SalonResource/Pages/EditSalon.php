<?php

namespace App\Filament\Admin\Resources\SalonResource\Pages;

use App\Filament\Admin\Resources\SalonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalon extends EditRecord
{
    protected static string $resource = SalonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
