<?php

namespace App\Filament\Admin\Resources\Influencers;

use App\Filament\Admin\Resources\Influencers\Pages\CreateInfluencer;
use App\Filament\Admin\Resources\Influencers\Pages\EditInfluencer;
use App\Filament\Admin\Resources\Influencers\Pages\ListInfluencers;
use App\Filament\Admin\Resources\Influencers\Schemas\InfluencerForm;
use App\Filament\Admin\Resources\Influencers\Tables\InfluencersTable;
use App\Models\Influencer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InfluencerResource extends Resource
{
    protected static ?string $model = Influencer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return InfluencerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InfluencersTable::configure($table);
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
            'index' => ListInfluencers::route('/'),
            'create' => CreateInfluencer::route('/create'),
            'edit' => EditInfluencer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
