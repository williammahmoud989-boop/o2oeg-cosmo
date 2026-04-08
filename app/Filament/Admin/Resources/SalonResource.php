<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SalonResource\Pages;
use App\Models\Salon;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Grid;
use App\Filament\Admin\Resources\SalonResource\RelationManagers\ServicesRelationManager;

class SalonResource extends Resource
{
    protected static ?string $model = Salon::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string | \UnitEnum | null $navigationGroup = 'Salon Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Section::make('Basic Information')
                    ->description('Main salon details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($set, ?string $state, $get) {
                                $slug = \Illuminate\Support\Str::slug($state);
                                $set('slug', $slug);
                                if (!$get('subdomain')) {
                                    $set('subdomain', $slug);
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name_ar')
                            ->label('Name (Arabic)')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3),
                        Textarea::make('description_ar')
                            ->label('Description (Arabic)')
                            ->rows(3),
                        Select::make('user_id')
                            ->relationship('owner', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Section::make('Branding & White-labeling')
                    ->description('Set up custom URLs for the salon booking engine')
                    ->schema([
                        TextInput::make('subdomain')
                            ->label('Subdomain')
                            ->placeholder('my-salon')
                            ->suffix('.cosmo.com')
                            ->unique(ignoreRecord: true)
                            ->helperText('The unique subdomain for this salon\'s booking engine.'),
                        TextInput::make('custom_domain')
                            ->label('Custom Domain')
                            ->placeholder('booking.mysalon.com')
                            ->unique(ignoreRecord: true)
                            ->helperText('Point your CNAME to cosmo.com to use a custom domain.'),
                    ])->columns(2),

                Section::make('Contact & Location')
                    ->schema([
                        TextInput::make('phone')
                            ->tel(),
                        TextInput::make('email')
                            ->email(),
                        TextInput::make('address'),
                        TextInput::make('address_ar')->label('Address (Arabic)'),
                        TextInput::make('city'),
                        TextInput::make('governorate'),
                        TextInput::make('website')->url(),
                        TextInput::make('latitude')
                            ->numeric(),
                        TextInput::make('longitude')
                            ->numeric(),
                    ])->columns(2),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('logo')
                            ->image()
                            ->directory('salons/logos'),
                        FileUpload::make('cover_image')
                            ->image()
                            ->directory('salons/covers'),
                    ])->columns(2),

                Section::make('Settings')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'pending' => 'Pending Review',
                            ])
                            ->default('pending')
                            ->required(),
                        Toggle::make('is_featured')
                            ->label('Featured Salon'),
                    ])->columns(2),

                Section::make('AI Dynamic Pricing')
                    ->description('Configure how the AI adjusts prices based on salon attendance')
                    ->schema([
                        TextInput::make('daily_capacity')
                            ->label('Daily Capacity (Bookings)')
                            ->numeric()
                            ->default(20)
                            ->helperText('The maximum number of bookings the salon can handle daily.'),
                        TextInput::make('occupancy_threshold_high')
                            ->label('Peak Occupancy Threshold (%)')
                            ->numeric()
                            ->default(70)
                            ->suffix('%')
                            ->helperText('When bookings exceed this percentage, peak pricing kicks in.'),
                        TextInput::make('peak_surcharge_percentage')
                            ->label('Peak Surcharge (%)')
                            ->numeric()
                            ->default(10)
                            ->suffix('%')
                            ->helperText('The extra percentage added to the price during peak occupancy.'),
                    ])->columns(3),

                Section::make('Payment & Deposits')
                    ->description('Manage how customers pay for bookings (Vodafone Cash / InstaPay)')
                    ->schema([
                        Toggle::make('requires_deposit')
                            ->label('Require Deposit')
                            ->helperText('Enable this to require a deposit for bookings on specific days.')
                            ->live(),
                        
                        TextInput::make('commission_rate')
                            ->label('Platform Commission (%)')
                            ->numeric()
                            ->default(10)
                            ->required(),

                        CheckboxList::make('payment_methods')
                            ->label('Enabled Payment Methods')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Credit Card',
                                'wallet' => 'Wallet (Vodafone Cash)',
                                'instapay' => 'InstaPay',
                            ])
                            ->columns(2),
                        
                        TextInput::make('deposit_percentage')
                            ->label('Deposit Percentage (%)')
                            ->numeric()
                            ->default(20)
                            ->visible(fn ($get) => $get('requires_deposit')),

                        CheckboxList::make('deposit_days')
                            ->label('Select Days for Deposit')
                            ->options([
                                'saturday' => 'Saturday (السبت)',
                                'sunday' => 'Sunday (الأحد)',
                                'monday' => 'Monday (الاثنين)',
                                'tuesday' => 'Tuesday (الثلاثاء)',
                                'wednesday' => 'Wednesday (الأربعاء)',
                                'thursday' => 'Thursday (الخميس)',
                                'friday' => 'Friday (الجمعة)',
                            ])
                            ->columns(2)
                            ->visible(fn ($get) => $get('requires_deposit')),

                        TextInput::make('vodafone_cash_number')
                            ->label('Vodafone Cash Number')
                            ->tel()
                            ->placeholder('010xxxxxxxx')
                            ->visible(fn ($get) => $get('requires_deposit')),

                        TextInput::make('instapay_id')
                            ->label('InstaPay ID / IPA')
                            ->placeholder('username@instapay')
                            ->visible(fn ($get) => $get('requires_deposit')),

                        Section::make('Paymob - Digital Payment Credentials')
                            ->description('Connect your own Paymob account to receive payments directly.')
                            ->schema([
                                TextInput::make('paymob_api_key')
                                    ->label('Paymob API Key')
                                    ->password()
                                    ->revealable()
                                    ->helperText('Your Paymob API Key from the dashboard.'),
                                TextInput::make('paymob_hmac_secret')
                                    ->label('Paymob HMAC Secret')
                                    ->password()
                                    ->revealable()
                                    ->helperText('Used for secure transaction verification.'),
                                TextInput::make('paymob_card_integration_id')
                                    ->label('Card Integration ID')
                                    ->helperText('Integration ID for Credit Card payments.'),
                                TextInput::make('paymob_iframe_id')
                                    ->label('Iframe ID')
                                    ->helperText('The ID of the payment iframe you created in Paymob.'),
                            ])->columns(2)->compact(),
                    ])->columns(2),

                Section::make('Social Presence')
                    ->description('Links to your social media profiles and WhatsApp')
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp Business Number')
                            ->tel()
                            ->placeholder('2010xxxxxxxx'),
                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->placeholder('https://instagram.com/your-salon'),
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->placeholder('https://facebook.com/your-salon'),
                        TextInput::make('tiktok_url')
                            ->label('TikTok URL')
                            ->url()
                            ->placeholder('https://tiktok.com/@your-salon'),
                    ])->columns(2),

                Section::make('Gallery')
                    ->description('Showcase your work with a beautiful image gallery')
                    ->schema([
                        FileUpload::make('gallery')
                            ->label('Salon Photos')
                            ->multiple()
                            ->image()
                            ->reorderable()
                            ->appendFiles()
                            ->directory('salons/gallery')
                            ->maxFiles(15)
                            ->columnSpanFull()
                            ->helperText('Upload high-quality images of your work, interior, and team.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('governorate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => 'inactive',
                    ]),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('rating')
                    ->sortable(),
                Tables\Columns\TextColumn::make('services_count')
                    ->counts('services')
                    ->label('Services'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'Pending',
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalons::route('/'),
            'create' => Pages\CreateSalon::route('/create'),
            'edit' => Pages\EditSalon::route('/{record}/edit'),
        ];
    }
}






