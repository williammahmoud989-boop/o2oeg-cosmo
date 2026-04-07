<?php

namespace App\Filament\Salon\Resources;

use App\Filament\Salon\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static string | \UnitEnum | null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('تفاصيل أمر الشراء')
                ->schema([
                    Select::make('supplier_id')
                        ->label('المورد')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    DatePicker::make('order_date')
                        ->label('تاريخ الطلب')
                        ->default(now())
                        ->required(),
                    Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'ordered' => 'تم الطلب (Ordered)',
                            'received' => 'تم الاستلام (Received)',
                            'cancelled' => 'ملغي (Cancelled)',
                        ])
                        ->default('ordered')
                        ->required(),
                    TextInput::make('total_amount')
                        ->label('الإجمالي')
                        ->numeric()
                        ->prefix('EGP')
                        ->disabled()
                        ->dehydrated(),
                ])->columns(2),

            Section::make('أصناف الطلب')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('product_id')
                                ->label('المنتج')
                                ->options(Product::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                    $set('unit_price', Product::find($state)?->cost_price ?? 0)
                                ),
                            TextInput::make('quantity')
                                ->label('الكمية')
                                ->numeric()
                                ->default(1)
                                ->required(),
                            TextInput::make('unit_price')
                                ->label('سعر الوحدة')
                                ->numeric()
                                ->prefix('EGP')
                                ->required(),
                        ])
                        ->columns(3)
                        ->addActionLabel('إضافة منتج')
                        ->live()
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            $items = $get('items') ?? [];
                            $total = 0;
                            foreach ($items as $item) {
                                $total += (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
                            }
                            $set('total_amount', $total);
                        }),
                ]),

            Section::make('ملاحظات')
                ->schema([
                    Textarea::make('notes')
                        ->label('ملاحظات إضافية')
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الطلب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('المورد')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ordered' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('المورد')
                    ->relationship('supplier', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'ordered' => 'تم الطلب',
                        'received' => 'تم الاستلام',
                        'cancelled' => 'ملغي',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_as_received')
                    ->label('استلام')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'ordered')
                    ->action(function ($record) {
                        $record->update(['status' => 'received']);
                        
                        // Logic to increase inventory
                        foreach ($record->items as $item) {
                            $item->product->increment('stock_quantity', $item->quantity);
                            
                            \App\Models\InventoryTransaction::create([
                                'salon_id' => $record->salon_id,
                                'product_id' => $item->product_id,
                                'type' => 'in',
                                'quantity' => $item->quantity,
                                'reference' => "PO-{$record->id}",
                                'notes' => 'استلام أمر شراء',
                            ]);
                        }
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
