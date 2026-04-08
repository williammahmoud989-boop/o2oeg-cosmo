<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StaffPerformanceMetricResource\Pages;
use App\Models\StaffPerformanceMetric;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
// ProgressColumn replaced with standard TextColumn
use Filament\Tables\Filters\SelectFilter;

class StaffPerformanceMetricResource extends Resource
{
    protected static ?string $model = StaffPerformanceMetric::class;

    protected static ?string $label = 'Staff Performance';

    protected static ?string $pluralLabel = 'Staff Performance Metrics';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Performance Data (Read Only)')
                ->schema([
                    TextInput::make('month')
                        ->disabled()
                        ->numeric(),

                    TextInput::make('year')
                        ->disabled()
                        ->numeric(),

                    TextInput::make('performance_score')
                        ->disabled()
                        ->numeric()
                        ->suffix('%')
                        ->hint('Calculation: (avg_rating/5 × 40%) + (completion_rate × 40%) + (attendance_rate × 20%)'),

                    TextInput::make('average_rating')
                        ->disabled()
                        ->numeric()
                        ->step(0.1)
                        ->suffix('/5'),

                    TextInput::make('completion_rate')
                        ->disabled()
                        ->numeric()
                        ->suffix('%'),

                    TextInput::make('attendance_rate')
                        ->disabled()
                        ->numeric()
                        ->suffix('%'),

                    TextInput::make('total_bookings')
                        ->disabled()
                        ->numeric(),

                    TextInput::make('completed_bookings')
                        ->disabled()
                        ->numeric(),

                    TextInput::make('total_revenue')
                        ->disabled()
                        ->prefix('$')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('total_commission')
                        ->disabled()
                        ->prefix('$')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('total_reviews')
                        ->disabled()
                        ->numeric(),

                    TextInput::make('present_days')
                        ->disabled()
                        ->numeric(),

                    TextInput::make('absent_days')
                        ->disabled()
                        ->numeric(),

                    TextInput::make('late_days')
                        ->disabled()
                        ->numeric(),
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

                TextColumn::make('month_year')
                    ->label('Month')
                    ->formatStateUsing(fn (string $state): string => $state)
                    ->sortable(),

                TextColumn::make('performance_score')
                    ->label('Score')
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->sortable()
                    ->color(function (float $state): string {
                        return match (true) {
                            $state >= 80 => 'success',
                            $state >= 60 => 'warning',
                            default => 'danger',
                        };
                    }),

                TextColumn::make('average_rating')
                    ->label('Avg Rating')
                    ->numeric(decimalPlaces: 1)
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('completion_rate')
                    ->label('Completion %')
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('attendance_rate')
                    ->label('Attendance %')
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->prefix('$')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('total_commission')
                    ->label('Commission')
                    ->prefix('$')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('total_bookings')
                    ->label('Bookings')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->defaultSort('month', 'desc')
            ->filters([
                SelectFilter::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name'),

                SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $years = StaffPerformanceMetric::distinct()
                            ->pluck('year')
                            ->sort()
                            ->reverse()
                            ->keyBy(fn ($year) => $year);
                        return $years->mapWithKeys(fn ($year) => [$year => (string)$year]);
                    }),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions - metrics are calculated, not manually edited
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
            'index' => Pages\ListStaffPerformanceMetrics::route('/'),
            'view' => Pages\ViewStaffPerformanceMetric::route('/{record}'),
        ];
    }
}













