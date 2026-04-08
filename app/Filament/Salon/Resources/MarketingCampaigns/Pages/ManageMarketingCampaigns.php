<?php

namespace App\Filament\Salon\Resources\MarketingCampaigns\Pages;

use App\Filament\Salon\Resources\MarketingCampaigns\MarketingCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMarketingCampaigns extends ManageRecords
{
    protected static string $resource = MarketingCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إنشاء حملة جديدة')
                ->icon('heroicon-o-plus')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['salon_id'] = auth()->user()->salon_id;
                    return $data;
                }),
        ];
    }

    public function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('salon_id', auth()->user()->salon_id);
    }
}

