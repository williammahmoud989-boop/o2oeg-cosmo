<?php

namespace App\Filament\Salon\Resources;

use App\Filament\Salon\Resources\UserPackageResource\Pages;
use App\Models\UserPackage;
use App\Models\Package;
use App\Models\UserPackageUsage;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;

class UserPackageResource extends Resource
{
    protected static ?string $model = UserPackage::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static string | \UnitEnum | null $navigationGroup = 'Marketing & Sales';
    
    protected static ?string $modelLabel = 'مشتريات الباقات';
    
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات شراء الباقة')
                ->schema([
                    Select::make('user_id')
                        ->label('العميل')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('package_id')
                        ->label('الباقة')
                        ->relationship('package', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $package = Package::find($state);
                            if ($package) {
                                $set('total_price', $package->price);
                                $set('purchase_date', now());
                                $set('expiry_date', now()->addDays($package->validity_days));
                            }
                        }),
                    TextInput::make('total_price')
                        ->label('السعر الإجمالي')
                        ->numeric()
                        ->prefix('EGP')
                        ->required(),
                    Select::make('payment_status')
                        ->label('حالة الدفع')
                        ->options([
                            'pending' => 'معلق (Pending)',
                            'paid' => 'تم الدفع (Paid)',
                        ])
                        ->default('paid')
                        ->required(),
                    DatePicker::make('purchase_date')
                        ->label('تاريخ الشراء')
                        ->default(now())
                        ->required(),
                    DatePicker::make('expiry_date')
                        ->label('تاريخ الانتهاء')
                        ->required(),
                    Toggle::make('is_active')
                        ->label('نشطة ومتاحة للاستخدام')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('package.name')
                    ->label('الباقة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('الاشتراك')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('الانتهاء')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => $state < now() ? 'danger' : 'success'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('consume')
                    ->label('خصم جلسة')
                    ->icon('heroicon-o-scissors')
                    ->color('warning')
                    ->form(function (UserPackage $record) {
                        $availableServices = [];
                        foreach ($record->package->items as $item) {
                            $used = UserPackageUsage::where('user_package_id', $record->id)
                                ->where('service_id', $item->service_id)
                                ->count();
                            
                            $left = $item->quantity - $used;
                            if ($left > 0) {
                                $availableServices[$item->service_id] = $item->service->name . " (متبقي $left)";
                            }
                        }

                        return [
                            Select::make('service_id')
                                ->label('اختر الخدمة المستهلكة')
                                ->options($availableServices)
                                ->required(),
                        ];
                    })
                    ->action(function (UserPackage $record, array $data) {
                        UserPackageUsage::create([
                            'user_package_id' => $record->id,
                            'service_id' => $data['service_id'],
                            'used_at' => now(),
                        ]);
                    })
                    ->visible(fn ($record) => $record->is_active && $record->expiry_date >= now()->startOfDay()),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserPackages::route('/'),
            'create' => Pages\CreateUserPackage::route('/create'),
            'edit' => Pages\EditUserPackage::route('/{record}/edit'),
        ];
    }
}




