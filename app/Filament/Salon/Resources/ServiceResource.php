<?php

namespace App\Filament\Salon\Resources;

use App\Filament\Salon\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Actions;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-scissors';

    protected static string | \UnitEnum | null $navigationGroup = 'Salon Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Section::make('تفاصيل الخدمة')
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم (English)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_ar')
                            ->label('الاسم (بالعربية)')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('الوصف (English)')
                            ->rows(3),
                        Textarea::make('description_ar')
                            ->label('الوصف (بالعربية)')
                            ->rows(3),
                        Select::make('category')
                            ->label('التصنيف')
                            ->options([
                                'شعر' => 'شعر (Hair)',
                                'بشرة' => 'بشرة (Skin)',
                                'أظافر' => 'أظافر (Nails)',
                                'مكياج' => 'مكياج (Makeup)',
                                'مساج' => 'مساج (Massage)',
                                'حمام مغربي' => 'حمام مغربي (Moroccan Bath)',
                                'تجهيز عرائس' => 'تجهيز عرائس (Bridal)',
                                'عام' => 'عام (General)',
                            ])
                            ->required()
                            ->searchable(),
                    ])->columns(2),

                Section::make('الأسعار والوقت')
                    ->schema([
                        TextInput::make('price')
                            ->label('السعر الأساسي')
                            ->numeric()
                            ->prefix('EGP')
                            ->required(),
                        TextInput::make('discount_price')
                            ->label('سعر العرض (اختياري)')
                            ->numeric()
                            ->prefix('EGP'),
                        TextInput::make('duration_minutes')
                            ->label('مدة الخدمة (بالدقائق)')
                            ->numeric()
                            ->suffix('دقيقة')
                            ->default(30)
                            ->required(),
                    ])->columns(3),

                Section::make('الوسائط والإعدادات')
                    ->schema([
                        FileUpload::make('image')
                            ->label('صورة الخدمة')
                            ->image()
                            ->directory('services'),
                        Toggle::make('is_active')
                            ->label('مفعلة')
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label('خدمة مميزة'),
                        TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_price')
                    ->money('EGP')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->suffix(' min')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
            ])
            ->filters([

                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}



