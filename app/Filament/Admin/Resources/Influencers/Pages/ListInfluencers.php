<?php

namespace App\Filament\Admin\Resources\Influencers\Pages;

use App\Filament\Admin\Resources\Influencers\InfluencerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInfluencers extends ListRecords
{
    protected static string $resource = InfluencerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
