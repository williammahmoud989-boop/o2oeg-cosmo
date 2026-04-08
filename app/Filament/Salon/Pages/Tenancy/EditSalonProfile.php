<?php

namespace App\Filament\Salon\Pages\Tenancy;

use App\Models\Salon;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Repeater;
use Filament\Forms\Components\TimePicker;

class EditSalonProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'إعدادات الصالون';
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
                            ->unique(Salon::class, 'slug', ignoreRecord: true),
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
                            ->label('العنوان'),
                        TextInput::make('address_ar')
                            ->label('العنوان (بالعربية)'),
                        TextInput::make('city')
                            ->label('المدينة'),
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel(),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني للصالون')
                            ->email(),
                        TextInput::make('website')
                            ->label('الموقع الإلكتروني')
                            ->url(),
                    ])->columns(2),

                Section::make('التواصل الاجتماعي')
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('رقم الواتساب للأعمال')
                            ->placeholder('2010xxxxxxxx')
                            ->tel(),
                        TextInput::make('facebook_url')
                            ->label('Facebook')
                            ->url(),
                        TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url(),
                        TextInput::make('tiktok_url')
                            ->label('TikTok')
                            ->url(),
                    ])->columns(2)->collapsed(),

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

                Section::make('أوقات العمل')
                    ->description('حدد مواعيد العمل الخاصة بك لكل يوم')
                    ->schema([
                        Repeater::make('working_hours')
                            ->schema([
                                Select::make('day')
                                    ->label('اليوم')
                                    ->options([
                                        'monday' => 'الاثنين',
                                        'tuesday' => 'الثلاثاء',
                                        'wednesday' => 'الأربعاء',
                                        'thursday' => 'الخميس',
                                        'friday' => 'الجمعة',
                                        'saturday' => 'السبت',
                                        'sunday' => 'الأحد',
                                    ])
                                    ->required(),
                                TimePicker::make('start_time')
                                    ->label('من')
                                    ->seconds(false)
                                    ->required(),
                                TimePicker::make('end_time')
                                    ->label('إلى')
                                    ->seconds(false)
                                    ->required(),
                                Toggle::make('is_closed')
                                    ->label('مغلق')
                                    ->default(false),
                            ])
                            ->columns(4)
                            ->label('المواعيد')
                            ->addActionLabel('إضافة موعد')
                            ->defaultItems(7),
                    ]),

                Section::make('إعدادات الدفع والعربون')
                    ->description('تحكم في كيفية دفع العملاء (نقدي أو تحويل لتأكيد الحجز)')
                    ->schema([
                        Toggle::make('requires_deposit')
                            ->label('طلب عربون تأكيد الحجز')
                            ->helperText('عند التفعيل، سيطلب من العميل دفع نسبة مئوية لتأكيد الحجز في أيام الازدحام.')
                            ->live(),
                        
                        TextInput::make('deposit_percentage')
                            ->label('نسبة العربون (%)')
                            ->numeric()
                            ->default(20)
                            ->visible(fn ($get) => $get('requires_deposit')),

                        CheckboxList::make('deposit_days')
                            ->label('أيام تفعيل العربون')
                            ->options([
                                'saturday' => 'السبت',
                                'sunday' => 'الأحد',
                                'monday' => 'الاثنين',
                                'tuesday' => 'الثلاثاء',
                                'wednesday' => 'الأربعاء',
                                'thursday' => 'الخميس',
                                'friday' => 'الجمعة',
                            ])
                            ->columns(2)
                            ->visible(fn ($get) => $get('requires_deposit')),

                        CheckboxList::make('payment_methods')
                            ->label('طرق الدفع المقبولة')
                            ->options([
                                'cash' => 'كاش (في الصالون)',
                                'card' => 'فيزا / ماستر كارد',
                                'wallet' => 'فودافون كاش',
                                'instapay' => 'انستا باي',
                            ])
                            ->live()
                            ->columns(2),

                        TextInput::make('vodafone_cash_number')
                            ->label('رقم فودافون كاش للمدفوعات')
                            ->tel()
                            ->placeholder('010xxxxxxxx')
                            ->visible(fn ($get) => in_array('wallet', $get('payment_methods') ?? []) || $get('requires_deposit')),

                        TextInput::make('instapay_id')
                            ->label('عنوان InstaPay')
                            ->placeholder('username@instapay')
                            ->visible(fn ($get) => in_array('instapay', $get('payment_methods') ?? []) || $get('requires_deposit')),
                    ])->columns(2),
            ]);
    }
}



