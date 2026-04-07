<?php

namespace App\Filament\Salon\Resources\PromoCodes\Tables;

use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromoCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('salon.name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('discount_percentage')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('commission_percentage')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('usage_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('usage_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_commission')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
