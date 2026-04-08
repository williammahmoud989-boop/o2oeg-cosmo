<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StaffAttendanceResource\Pages;
use App\Models\StaffAttendance;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class StaffAttendanceResource extends Resource
{
    protected static ?string $model = StaffAttendance::class;

    protected static ?string $label = 'Staff Attendance';

    protected static ?string $pluralLabel = 'Staff Attendance';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Attendance Details')
                ->schema([
                    Select::make('staff_id')
                        ->label('Staff')
                        ->relationship('staff', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),

                    DatePicker::make('date')
                        ->required(),

                    TimePicker::make('check_in_time')
                        ->label('Check In'),

                    TimePicker::make('check_out_time')
                        ->label('Check Out'),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'present' => 'Present',
                            'absent' => 'Absent',
                            'late' => 'Late',
                            'half_day' => 'Half Day',
                            'on_leave' => 'On Leave',
                        ])
                        ->required(),

                    Textarea::make('notes')
                        ->rows(3),
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

                TextColumn::make('date')
                    ->label('Date')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('check_in_time')
                    ->label('Check In')
                    ->time('H:i')
                    ->alignCenter(),

                TextColumn::make('check_out_time')
                    ->label('Check Out')
                    ->time('H:i')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->color(function (string $state): string {
                        return match ($state) {
                            'present' => 'success',
                            'absent' => 'danger',
                            'late' => 'warning',
                            'half_day' => 'info',
                            'on_leave' => 'secondary',
                        };
                    })
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'present' => 'Present',
                            'absent' => 'Absent',
                            'late' => 'Late',
                            'half_day' => 'Half Day',
                            'on_leave' => 'On Leave',
                        };
                    })
                    ->alignCenter(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'half_day' => 'Half Day',
                        'on_leave' => 'On Leave',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('From Date'),
                        DatePicker::make('date_to')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->where('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->where('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListStaffAttendances::route('/'),
            'create' => Pages\CreateStaffAttendance::route('/create'),
            'edit' => Pages\EditStaffAttendance::route('/{record}/edit'),
        ];
    }
}














