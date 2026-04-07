<?php

namespace App\Filament\Salon\Resources\Staff\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الموظفة / الموظف')
                    ->placeholder('مثال: سارة محمد')
                    ->required()
                    ->icon('heroicon-o-user'),
                TextInput::make('specialization')
                    ->label('التخصص')
                    ->placeholder('مثال: تصفيف شعر / مكياج')
                    ->icon('heroicon-o-sparkles'),
                TextInput::make('commission_rate')
                    ->label('نسبة العموله')
                    ->required()
                    ->numeric()
                    ->default(10)
                    ->suffix('%')
                    ->helperText('النسبة المئوية التي يحصل عليها الموظف من إجمالي سعر الخدمة')
                    ->icon('heroicon-o-receipt-percent'),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->required(),
                Toggle::make('attendance_reminder_enabled')
                    ->label('تفعيل تذكير الحضور')
                    ->helperText('إرسال رسائل واتساب يومية لتذكير الموظف بالحضور')
                    ->default(false),
                TimePicker::make('attendance_time')
                    ->label('وقت الحضور')
                    ->placeholder('مثال: 08:00')
                    ->visible(fn ($get) => $get('attendance_reminder_enabled')),
                TextInput::make('whatsapp_number')
                    ->label('رقم واتساب')
                    ->placeholder('مثال: +966501234567')
                    ->tel()
                    ->visible(fn ($get) => $get('attendance_reminder_enabled'))
                    ->helperText('الرقم المسجل في واتساب لإرسال التذكيرات'),
                Toggle::make('privacy_consent')
                    ->label('موافقة على الخصوصية')
                    ->helperText('أوافق على استخدام رقم واتساب لإرسال تذكيرات الحضور والمكالمات إذا لزم الأمر. هذا يتوافق مع قوانين حماية البيانات.')
                    ->visible(fn ($get) => $get('attendance_reminder_enabled'))
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $set('consent_given_at', now());
                        } else {
                            $set('consent_given_at', null);
                        }
                    }),
                \Filament\Forms\Components\Hidden::make('consent_given_at'),
            ]);
    }
}
