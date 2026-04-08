<?php

namespace App\Filament\Salon\Resources\Staff\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الموظف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('specialization')
                    ->label('التخصص')
                    ->searchable(),
                TextColumn::make('commission_rate')
                    ->label('العمولة')
                    ->suffix('%')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
                IconColumn::make('attendance_reminder_enabled')
                    ->label('تذكير الحضور')
                    ->boolean(),
                TextColumn::make('attendance_time')
                    ->label('وقت الحضور')
                    ->time('H:i'),
                TextColumn::make('whatsapp_number')
                    ->label('واتساب')
                    ->placeholder('غير محدد'),
                IconColumn::make('privacy_consent')
                    ->label('موافقة الخصوصية')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

