<?php

namespace App\Filament\Salon\Resources\PromoCodes;

use App\Filament\Salon\Resources\PromoCodes\Pages\CreatePromoCode;
use App\Filament\Salon\Resources\PromoCodes\Pages\EditPromoCode;
use App\Filament\Salon\Resources\PromoCodes\Pages\ListPromoCodes;
use App\Filament\Salon\Resources\PromoCodes\Schemas\PromoCodeForm;
use App\Filament\Salon\Resources\PromoCodes\Tables\PromoCodesTable;
use App\Models\PromoCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return PromoCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromoCodesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromoCodes::route('/'),
            'create' => CreatePromoCode::route('/create'),
            'edit' => EditPromoCode::route('/{record}/edit'),
        ];
    }
}


