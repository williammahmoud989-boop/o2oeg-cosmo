<?php

namespace App\Filament\Salon\Resources\PromoCodes\Schemas;

use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
                Grid::make(3)
                    ->schema([
                        TextInput::make('code')
                            ->label('كود الخصم (Promo Code)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g. SUMMER2024'),
                        TextInput::make('discount_percentage')
                            ->label('نسبة الخصم (%)')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->minValue(1)
                            ->maxValue(100),
                        TextInput::make('usage_limit')
                            ->label('حد الاستخدام (Usage Limit)')
                            ->numeric()
                            ->helperText('اتركه فارغاً للاستخدام غير المحدود'),
                    ]),

                Grid::make(2)
                    ->schema([
                        DateTimePicker::make('expires_at')
                            ->label('تاريخ الانتهاء (Expiry Date)'),
                        Toggle::make('is_active')
                            ->label('تفعيل الكود')
                            ->default(true)
                            ->required(),
                    ]),

                Section::make('إحصائيات الاستخدام (Tracking)')
                    ->description('هذه البيانات يتم تحديثها تلقائياً عند استخدام الكود')
                    ->schema([
                        TextInput::make('usage_count')
                            ->label('عدد المرات المستخدمة')
                            ->disabled()
                            ->dehydrated(false)
                            ->numeric()
                            ->default(0),
                        TextInput::make('commission_percentage')
                            ->label('نسبة العمولة (Commission %)')
                            ->numeric()
                            ->default(0)
                            ->helperText('للشركاء أو المسوقين'),
                        TextInput::make('total_commission')
                            ->label('إجمالي العمولة المحققة')
                            ->disabled()
                            ->dehydrated(false)
                            ->numeric()
                            ->default(0),
                    ])->columns(3)->collapsed(),
            ]);
    }
}
