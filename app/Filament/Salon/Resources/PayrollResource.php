<?php

namespace App\Filament\Salon\Resources;

use App\Filament\Salon\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use App\Models\Staff;
use App\Models\Booking;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | \UnitEnum | null $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات الراتب')
                ->schema([
                    Select::make('staff_id')
                        ->label('الموظف')
                        ->relationship('staff', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                            $staff = Staff::find($state);
                            if ($staff) {
                                $set('base_salary', $staff->base_salary);
                                self::calculateTotals($set, $get);
                            }
                        }),
                    
                    Select::make('month')
                        ->label('الشهر')
                        ->options([
                            '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس', '04' => 'أبريل',
                            '05' => 'مايو', '06' => 'يونيو', '07' => 'يوليو', '08' => 'أغسطس',
                            '09' => 'سبتمبر', '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر',
                        ])
                        ->default(date('m'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn (Forms\Set $set, callable $get) => self::calculateTotals($set, $get)),

                    TextInput::make('year')
                        ->label('السنة')
                        ->numeric()
                        ->default(date('Y'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn (Forms\Set $set, callable $get) => self::calculateTotals($set, $get)),
                ])->columns(3),

            Section::make('التفاصيل المالية')
                ->schema([
                    TextInput::make('base_salary')
                        ->label('الراتب الأساسي')
                        ->numeric()
                        ->prefix('EGP')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn (Forms\Set $set, callable $get) => self::calculateTotals($set, $get)),
                    
                    TextInput::make('total_commission')
                        ->label('إجمالي العمولات')
                        ->numeric()
                        ->prefix('EGP')
                        ->default(0)
                        ->disabled()
                        ->dehydrated(),
                        
                    TextInput::make('advances')
                        ->label('السلف')
                        ->numeric()
                        ->prefix('EGP')
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(fn (Forms\Set $set, callable $get) => self::calculateTotals($set, $get)),

                    TextInput::make('deductions')
                        ->label('الخصومات')
                        ->numeric()
                        ->prefix('EGP')
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(fn (Forms\Set $set, callable $get) => self::calculateTotals($set, $get)),
                        
                    TextInput::make('net_salary')
                        ->label('الصافي المستحق')
                        ->numeric()
                        ->prefix('EGP')
                        ->disabled()
                        ->dehydrated()
                        ->weight('bold'),
                ])->columns(2),

            Section::make('الدفع والملاحظات')
                ->schema([
                    Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'pending' => 'معلق (Pending)',
                            'paid' => 'تم الدفع (Paid)',
                        ])
                        ->default('pending')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state === 'paid') {
                                $set('payment_date', now());
                            } else {
                                $set('payment_date', null);
                            }
                        }),
                        
                    DatePicker::make('payment_date')
                        ->label('تاريخ الدفع')
                        ->visible(fn (callable $get) => $get('status') === 'paid'),
                        
                    Textarea::make('notes')
                        ->label('ملاحظات إضافية')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    protected static function calculateTotals(Forms\Set $set, callable $get)
    {
        $staffId = $get('staff_id');
        $month = $get('month');
        $year = $get('year');
        
        if ($staffId && $month && $year) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            $commissions = Booking::where('staff_id', $staffId)
                ->where('status', 'completed')
                ->whereBetween('booking_time', [$startDate, $endDate])
                ->sum('commission_amount');
                
            $set('total_commission', $commissions);
        }

        $base = (float) ($get('base_salary') ?? 0);
        $comm = (float) ($get('total_commission') ?? 0);
        $advances = (float) ($get('advances') ?? 0);
        $deductions = (float) ($get('deductions') ?? 0);
        
        $net = $base + $comm - $advances - $deductions;
        $set('net_salary', max(0, $net));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('الشهر')
                    ->formatStateUsing(fn ($state, $record) => $state . ' / ' . $record->year)
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_salary')
                    ->label('الصافي المستحق')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'paid' => 'تم الدفع',
                    ]),
                Tables\Filters\SelectFilter::make('staff_id')
                    ->label('الموظف')
                    ->relationship('staff', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_as_paid')
                    ->label('تم الدفع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'paid',
                            'payment_date' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
