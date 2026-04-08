<?php

namespace App\Filament\Salon\Resources\PricingRules\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PricingRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
                Section::make('تفاصيل القاعدة (Rule Details)')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم القاعدة (مثلاً: ساعة السعادة)')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('نوع القاعدة')
                            ->options([
                                'happy_hour' => 'ساعة السعادة (Happy Hour)',
                                'weekend' => 'عرض نهاية الأسبوع',
                                'seasonal' => 'عرض موسمي',
                                'custom' => 'مخصص',
                            ])
                            ->required(),
                        TextInput::make('percentage')
                            ->label('نسبة الخصم (%)')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->default(10),
                    ])->columns(3),

                Section::make('الوقت والتنفيذ (Timing)')
                    ->schema([
                        Select::make('day_of_week')
                            ->label('اليوم')
                            ->options([
                                'monday' => 'الاثنين',
                                'tuesday' => 'الثلاثاء',
                                'wednesday' => 'الأربعاء',
                                'thursday' => 'الخميس',
                                'friday' => 'الجمعة',
                                'saturday' => 'السبت',
                                'sunday' => 'الأحد',
                                'all' => 'كل الأيام',
                            ]),
                        Grid::make(2)
                            ->schema([
                                TimePicker::make('start_time')
                                    ->label('وقت البدء'),
                                TimePicker::make('end_time')
                                    ->label('وقت الانتهاء'),
                            ]),
                        Toggle::make('is_active')
                            ->label('تفعيل القاعدة')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }
}



