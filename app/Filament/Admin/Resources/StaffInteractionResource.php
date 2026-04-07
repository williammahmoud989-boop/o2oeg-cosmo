<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StaffInteractionResource\Pages;
use App\Models\StaffInteraction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StaffInteractionResource extends Resource
{
    protected static ?string $model = StaffInteraction::class;

    protected static ?string $navigationLabel = 'تفاعلات الموظفين';

    protected static ?string $modelLabel = 'تفاعل موظف';

    protected static ?string $pluralModelLabel = 'تفاعلات الموظفين';

    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('staff_id')
                    ->relationship('staff', 'name')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'message_sent' => 'رسالة مرسلة',
                        'call_made' => 'مكالمة',
                        'response_received' => 'رد مستلم',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'success' => 'نجح',
                        'failed' => 'فشل',
                        'pending' => 'معلق',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('details')
                    ->label('التفاصيل'),
                Forms\Components\DateTimePicker::make('sent_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('الموظف')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'message_sent' => 'success',
                        'call_made' => 'warning',
                        'response_received' => 'info',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListStaffInteractions::route('/'),
            'create' => Pages\CreateStaffInteraction::route('/create'),
            'view' => Pages\ViewStaffInteraction::route('/{record}'),
            'edit' => Pages\EditStaffInteraction::route('/{record}/edit'),
        ];
    }
}