<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AnalyticsMetricResource\Pages;
use App\Models\AnalyticsMetric;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AnalyticsMetricResource extends Resource
{
    protected static ?string $model = AnalyticsMetric::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $label = 'Daily Analytics';

    protected static ?string $pluralLabel = 'Daily Analytics';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Analytics Data')
                ->schema([
                    // This resource is primarily for viewing, not editing
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date('Y-m-d')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('salon.name')
                    ->label('Salon')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_bookings')
                    ->label('Total Bookings')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('completed_bookings')
                    ->label('Completed')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('cancelled_bookings')
                    ->label('Cancelled')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('danger')
                    ->alignCenter(),

                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('EGP')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('net_revenue')
                    ->label('Net Revenue')
                    ->money('EGP')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('average_booking_value')
                    ->label('Avg Booking')
                    ->money('EGP')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('unique_customers')
                    ->label('Unique Customers')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('returning_customers')
                    ->label('Returning')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('occupancy_rate')
                    ->label('Occupancy %')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->suffix('%')
                    ->alignCenter(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('salon_id')
                    ->label('Salon')
                    ->relationship('salon', 'name'),

                Filter::make('date_range')
                    ->form([
                        \Filament\Schemas\Components\DatePicker::make('date_from')
                            ->label('From Date'),
                        \Filament\Schemas\Components\DatePicker::make('date_to')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // View only - no edit/delete actions needed
            ])
            ->bulkActions([
                // No bulk actions for analytics data
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
            'index' => Pages\ListAnalyticsMetrics::route('/'),
        ];
    }
}
