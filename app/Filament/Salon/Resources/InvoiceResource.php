<?php

namespace App\Filament\Salon\Resources;

use App\Filament\Salon\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات الفاتورة')
                ->schema([
                    TextInput::make('invoice_number')
                        ->label('رقم الفاتورة')
                        ->disabled()
                        ->dehydrated()
                        ->visibleOn('edit'),
                    Select::make('user_id')
                        ->label('العميل')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'paid' => 'تم الدفع (Paid)',
                            'pending' => 'معلق (Pending)',
                            'void' => 'ملغي (Void)',
                        ])
                        ->default('paid')
                        ->required(),
                    Select::make('payment_method')
                        ->label('وسيلة الدفع')
                        ->options([
                            'cash' => 'نقدي (Cash)',
                            'card' => 'بطاقة (Card)',
                            'wallet' => 'محفظة (Wallet)',
                        ]),
                ])->columns(2),

            Section::make('بنود الفاتورة')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('product_id')
                                ->label('المنتج')
                                ->options(Product::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('unit_price', $product->price);
                                        $set('subtotal', $product->price);
                                    }
                                }),
                            TextInput::make('quantity')
                                ->label('الكمية')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => 
                                    $set('subtotal', (float) $state * (float) $get('unit_price'))
                                ),
                            TextInput::make('unit_price')
                                ->label('سعر الوحدة')
                                ->numeric()
                                ->prefix('EGP')
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => 
                                    $set('subtotal', (float) $state * (float) $get('quantity'))
                                ),
                            TextInput::make('subtotal')
                                ->label('المجموع الفرعي')
                                ->numeric()
                                ->prefix('EGP')
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columns(4)
                        ->addActionLabel('إضافة بند')
                        ->live()
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            $items = $get('items') ?? [];
                            $total = 0;
                            foreach ($items as $item) {
                                $total += (float) ($item['subtotal'] ?? 0);
                            }
                            $set('total_amount', $total);
                            
                            $tax = $get('tax_amount') ?? 0;
                            $discount = $get('discount_amount') ?? 0;
                            $set('payable_amount', $total + (float) $tax - (float) $discount);
                        }),
                ]),

            Section::make('الحسابات والخصومات')
                ->schema([
                    TextInput::make('total_amount')
                        ->label('الإجمالي (قبل الضرائب والخصم)')
                        ->numeric()
                        ->prefix('EGP')
                        ->disabled()
                        ->dehydrated(),
                    TextInput::make('tax_amount')
                        ->label('قيمة الضريبة')
                        ->numeric()
                        ->default(0)
                        ->prefix('EGP')
                        ->live()
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            $total = (float) $get('total_amount') ?? 0;
                            $tax = (float) $get('tax_amount') ?? 0;
                            $discount = (float) $get('discount_amount') ?? 0;
                            $set('payable_amount', $total + $tax - $discount);
                        }),
                    TextInput::make('discount_amount')
                        ->label('مبلغ الخصم')
                        ->numeric()
                        ->default(0)
                        ->prefix('EGP')
                        ->live()
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            $total = (float) $get('total_amount') ?? 0;
                            $tax = (float) $get('tax_amount') ?? 0;
                            $discount = (float) $get('discount_amount') ?? 0;
                            $set('payable_amount', $total + $tax - $discount);
                        }),
                    TextInput::make('payable_amount')
                        ->label('المبلغ الصافي المستحق')
                        ->numeric()
                        ->prefix('EGP')
                        ->disabled()
                        ->dehydrated()
                        ->weight('bold'),
                ])->columns(2),

            Section::make('ملاحظات')
                ->schema([
                    Textarea::make('notes')
                        ->label('ملاحظات الفاتورة')
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payable_amount')
                    ->label('المبلغ المستحق')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'void' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'paid' => 'تم الدفع',
                        'pending' => 'معلق',
                        'void' => 'ملغي',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print')
                    ->label('طباعة')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('invoices.print', $record), shouldOpenInNewTab: true)
                    ->color('info'),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
