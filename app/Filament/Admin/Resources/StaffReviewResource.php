<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StaffReviewResource\Pages;
use App\Models\StaffReview;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class StaffReviewResource extends Resource
{
    protected static ?string $model = StaffReview::class;

    protected static ?string $label = 'Staff Reviews';

    protected static ?string $pluralLabel = 'Staff Reviews';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Review Details')
                ->schema([
                    // View only
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('staff.name')
                    ->label('Staff')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('booking.booking_code')
                    ->label('Booking')
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('comment')
                    ->label('Comment')
                    ->limit(50)
                    ->tooltip(fn (StaffReview $record): ?string => $record->comment),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name'),

                SelectFilter::make('rating')
                    ->label('Rating')
                    ->options([
                        5 => '5 Stars',
                        4 => '4 Stars',
                        3 => '3 Stars',
                        2 => '2 Stars',
                        1 => '1 Star',
                    ]),
            ])
            ->actions([
                // View only
            ])
            ->bulkActions([
                // No bulk actions
            ])
            ->striped();
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
            'index' => Pages\ListStaffReviews::route('/'),
        ];
    }
}











