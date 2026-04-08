<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Section::make('Booking Details')
                    ->schema([
                        TextInput::make('booking_code')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('salon_id')
                            ->relationship('salon', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('influencer_id')
                            ->relationship('influencer', 'name')
                            ->label('Influencer (By Referral)')
                            ->searchable()
                            ->preload()
                            ->placeholder('None'),
                    ])->columns(2),

                Section::make('Schedule')
                    ->schema([
                        DatePicker::make('booking_date')
                            ->required()
                            ->minDate(\now()),
                        TimePicker::make('start_time')
                            ->required()
                            ->seconds(false),
                        TimePicker::make('end_time')
                            ->required()
                            ->seconds(false),
                    ])->columns(3),

                Section::make('Payment & Status')
                    ->schema([
                        TextInput::make('total_price')
                            ->numeric()
                            ->prefix('EGP')
                            ->required(),
                        TextInput::make('deposit_amount')
                            ->label('Deposit Amount (العربون)')
                            ->numeric()
                            ->prefix('EGP')
                            ->helperText('The amount paid as a deposit via VFC/InstaPay.'),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending (بانتظار التأكيد)',
                                'confirmed' => 'Confirmed (مؤكد)',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'no_show' => 'No Show',
                            ])
                            ->default('pending')
                            ->required(),
                        Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid (تم استلام العربون)',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending')
                            ->required(),
                        TextInput::make('payment_method')
                            ->label('Payment Method (VFC or InstaPay)'),
                        TextInput::make('influencer_commission')
                            ->label('Influencer Commission')
                            ->numeric()
                            ->prefix('EGP')
                            ->helperText('Calculated based on influencer rate.'),
                        FileUpload::make('payment_receipt')
                            ->label('Payment Receipt (صورة التحويل)')
                            ->image()
                            ->directory('bookings/receipts')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record && $record->payment_receipt),
                    ])->columns(2),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3),
                        Textarea::make('cancellation_reason')
                            ->rows(2)
                            ->visible(fn ($get) => $get('status') === 'cancelled'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_code')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('salon.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('booking_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('influencer.name')
                    ->label('Referral')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'     => 'warning',
                        'confirmed'   => 'info',
                        'in_progress' => 'primary',
                        'completed'   => 'success',
                        default       => 'danger',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'paid'     => 'success',
                        'refunded' => 'danger',
                        default    => 'gray',
                    }),
                Tables\Columns\ImageColumn::make('payment_receipt')
                    ->label('الوصل')
                    ->disk('public')
                    ->circular()
                    ->size(50),
            ])
            ->filters([
// ... (keep filters) ...
                Tables\Filters\SelectFilter::make('salon_id')
                    ->relationship('salon', 'name')
                    ->label('Salon'),
            ])
            ->actions([
                Actions\Action::make('verifyPayment')
                    ->label('تأكيد الدفع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->payment_status === 'paid' || !$record->payment_receipt)
                    ->action(function ($record) {
                        $record->update([
                            'payment_status' => 'paid',
                            'status' => 'confirmed'
                        ]);

                        Notification::make()
                            ->title('تم تأكيد الدفع بنجاح')
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('cancelBooking')
                    ->label('إلغاء الحجز')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('cancellation_reason')
                            ->label('سبب الإلغاء')
                            ->required(),
                    ])
                    ->hidden(fn ($record) => in_array($record->status, ['cancelled', 'completed']))
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'cancelled',
                            'cancellation_reason' => $data['cancellation_reason'],
                            'cancelled_at' => \now(),
                        ]);

                        Notification::make()
                            ->title('تم إلغاء الحجز')
                            ->danger()
                            ->send();
                    }),
                Actions\EditAction::make(),
                Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('booking_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}






