<?php

namespace App\Filament\Salon\Resources;

use App\Filament\Salon\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;

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

                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Section::make('التفاصيل والمواعيد')
                    ->schema([
                        DatePicker::make('booking_date')
                            ->label('تاريخ الحجز')
                            ->required()
                            ->minDate(\now()),
                        TimePicker::make('start_time')
                            ->label('وقت البدء')
                            ->required()
                            ->seconds(false),
                        TimePicker::make('end_time')
                            ->label('وقت الانتهاء')
                            ->required()
                            ->seconds(false),
                    ])->columns(3),

                Section::make('حالة الدفع والحجز')
                    ->schema([
                        TextInput::make('total_price')
                            ->label('السعر الإجمالي')
                            ->numeric()
                            ->prefix('EGP')
                            ->required(),
                        TextInput::make('deposit_amount')
                            ->label('مبلغ العربون')
                            ->numeric()
                            ->prefix('EGP')
                            ->helperText('المبلغ الذي دفعه العميل كمقدم (عربون)'),
                        Select::make('status')
                            ->label('حالة الحجز')
                            ->options([
                                'pending' => 'بانتظار التأكيد (Pending)',
                                'confirmed' => 'مؤكد (Confirmed)',
                                'in_progress' => 'قيد التنفيذ (In Progress)',
                                'completed' => 'مكتمل (Completed)',
                                'cancelled' => 'ملغي (Cancelled)',
                                'no_show' => 'لم يحضر (No Show)',
                            ])
                            ->default('pending')
                            ->required(),
                        Select::make('payment_status')
                            ->label('حالة الدفع')
                            ->options([
                                'pending' => 'بانتظار الدفع (Pending)',
                                'paid' => 'تم الدفع/التحويل (Paid)',
                                'refunded' => 'تم الاسترداد (Refunded)',
                            ])
                            ->default('pending')
                            ->required(),
                        TextInput::make('payment_method')
                            ->label('وسيلة الدفع المتوقعة'),
                        FileUpload::make('payment_receipt')
                            ->label('إيصال التحويل (صورة)')
                            ->image()
                            ->directory('bookings/receipts')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record && $record->payment_receipt),
                    ])->columns(2),

                Section::make('الموظف والعمولة')
                    ->description('تحديد الموظف الذي قام بالخدمة لحساب عمولته تلقائياً')
                    ->schema([
                        Select::make('staff_id')
                            ->label('الموظف المسؤول')
                            ->relationship('staff', 'name', fn ($query) => $query->where('is_active', true))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('اختر الموظف لحساب العمولة بناءً على نسبته المسجلة'),
                        TextInput::make('commission_amount')
                            ->label('قيمة العمولة')
                            ->numeric()
                            ->prefix('EGP')
                            ->disabled()
                            ->helperText('يتم حسابها تلقائياً عند اختيار الموظف وحفظ الحجز'),
                    ])->columns(2),

                Section::make('ملاحظات إضافية')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات الحجز')
                            ->rows(3),
                        Textarea::make('cancellation_reason')
                            ->label('سبب الإلغاء')
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

                Tables\Columns\TextColumn::make('service.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('booking_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('الموظف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('العمولة')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'refunded' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\ImageColumn::make('payment_receipt')
                    ->label('الإيصال')
                    ->square()
                    ->disk('public')
                    ->toggleable(),
            ])
            ->filters([])
            ->actions([
                Actions\Action::make('viewReceipt')
                    ->label('عرض الإيصال')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('إيصال الدفع')
                    ->modalContent(fn ($record) => view('filament.components.receipt-modal', ['record' => $record]))
                    ->hidden(fn ($record) => !$record->payment_receipt),

                Actions\Action::make('verifyPayment')
                    ->label('تأكيد الدفع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->payment_status === 'paid' || !$record->payment_receipt)
                    ->action(function ($record) {
                        $record->update([
                            'payment_status' => 'paid',
                            'status' => 'confirmed',
                            'confirmed_at' => \now(),
                        ]);

                        // Send notification to the user
                        if ($record->user) {
                            Notification::make()
                                ->title('تم تأكيد حجزك!')
                                ->body("تم مراجعة إيصال الدفع لحجزك ({$record->booking_code}) وتأكيده بنجاح. نتطلع لرؤيتك!")
                                ->success()
                                ->sendToDatabase($record->user);
                        }

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
                    ->hidden(fn ($record) => \in_array($record->status, ['cancelled', 'completed']))
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



