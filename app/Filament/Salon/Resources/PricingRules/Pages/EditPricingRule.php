<?php

namespace App\Filament\Salon\Resources\PricingRules\Pages;

use App\Filament\Salon\Resources\PricingRules\PricingRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPricingRule extends EditRecord
{
    protected static string $resource = PricingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

