<?php

namespace App\Filament\Salon\Pages\Tenancy;

use App\Models\Salon;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;

class RegisterSalon extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Salon';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
                Section::make('المعلومات الأساسية')
                    ->description('تفاصيل الصالون الرئيسية')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الصالون')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        TextInput::make('slug')
                            ->label('رابط الصالون (Slug)')
                            ->required()
                            ->maxLength(255)
                            ->unique(Salon::class, 'slug'),
                        Textarea::make('description')
                            ->label('وصف الصالون')
                            ->rows(3),
                        Textarea::make('description_ar')
                            ->label('وصف الصالون (بالعربية)')
                            ->rows(3),
                    ])->columns(2),

                Section::make('معلومات الاتصال والموقع')
                    ->schema([
                        TextInput::make('address')
                            ->label('العنوان')
                            ->required(),
                        TextInput::make('city')
                            ->label('المدينة')
                            ->required(),
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->required()
                            ->tel(),
                        TextInput::make('whatsapp_number')
                            ->label('رقم الواتساب')
                            ->tel()
                            ->placeholder('2010xxxxxxxx'),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني للصالون')
                            ->email(),
                        TextInput::make('website')
                            ->label('الموقع الإلكتروني')
                            ->url(),
                    ])->columns(2),

                Section::make('التواصل الاجتماعي')
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook')
                            ->url(),
                        TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url(),
                        TextInput::make('tiktok_url')
                            ->label('TikTok')
                            ->url(),
                    ])->columns(3)->collapsed(),

                Section::make('الهوية البصرية')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('اللوجو')
                            ->image()
                            ->directory('salons/logos'),
                        FileUpload::make('cover_image')
                            ->label('صورة الغلاف')
                            ->image()
                            ->directory('salons/covers'),
                    ])->columns(2),

                Section::make('معرض الصور')
                    ->description('اعرض أفضل أعمالك وفريقك لجذب العملاء')
                    ->schema([
                        FileUpload::make('gallery')
                            ->label('صور الصالون')
                            ->multiple()
                            ->image()
                            ->reorderable()
                            ->appendFiles()
                            ->directory('salons/gallery')
                            ->maxFiles(15)
                            ->columnSpanFull(),
                    ]),

                Section::make('إعدادات الدفع والعربون')
                    ->description('تحديد طرق الدفع المفضلة وسياسة العربون')
                    ->schema([
                        Toggle::make('requires_deposit')
                            ->label('تفعيل نظام العربون لتأكيد الحجز')
                            ->live(),

                        CheckboxList::make('payment_methods')
                            ->label('طرق الدفع المتوفرة')
                            ->options([
                                'cash' => 'كاش (في الصالون)',
                                'card' => 'فيزا / ماستر كارد',
                                'wallet' => 'فودافون كاش',
                                'instapay' => 'انستا باي',
                            ])
                            ->live()
                            ->columns(2),

                        TextInput::make('vodafone_cash_number')
                            ->label('رقم فودافون كاش')
                            ->placeholder('010xxxxxxxx')
                            ->visible(fn ($get) => in_array('wallet', $get('payment_methods') ?? []) || $get('requires_deposit')),

                        TextInput::make('instapay_id')
                            ->label('عنوان InstaPay')
                            ->placeholder('username@instapay')
                            ->visible(fn ($get) => in_array('instapay', $get('payment_methods') ?? []) || $get('requires_deposit')),
                    ])->columns(2),
            ]);
    }

    protected function handleRegistration(array $data): Salon
    {
        return Salon::create($data);
    }
}



