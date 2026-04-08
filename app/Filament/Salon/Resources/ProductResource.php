<?php

namespace App\Filament\Salon\Resources;

use App\Filament\Salon\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string | \UnitEnum | null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات المنتج')
                ->schema([
                    TextInput::make('name')
                        ->label('اسم المنتج')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('sku')
                        ->label('SKU / الباركود')
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Select::make('category_id')
                        ->label('التصنيف')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ])->columns(2),

            Section::make('الأسعار والمخزون')
                ->schema([
                    TextInput::make('price')
                        ->label('سعر البيع')
                        ->numeric()
                        ->prefix('EGP')
                        ->required(),
                    TextInput::make('cost_price')
                        ->label('سعر التكلفة')
                        ->numeric()
                        ->prefix('EGP'),
                    TextInput::make('stock_quantity')
                        ->label('الكمية الحالية')
                        ->numeric()
                        ->default(0)
                        ->required(),
                    TextInput::make('alert_quantity')
                        ->label('كمية التنبيه (Low Stock)')
                        ->numeric()
                        ->default(5)
                        ->required(),
                    Toggle::make('is_active')
                        ->label('مفعل')
                        ->default(true),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('التصنيف')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('الكمية')
                    ->sortable()
                    ->color(fn ($state, $record) => $state <= $record->alert_quantity ? 'danger' : 'success')
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
        ];
    }
}




