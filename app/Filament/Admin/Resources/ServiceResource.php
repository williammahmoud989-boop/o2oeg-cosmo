<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Toggle;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-scissors';

    protected static string | \UnitEnum | null $navigationGroup = 'Salon Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Section::make('Service Details')
                    ->schema([
                        Select::make('salon_id')
                            ->relationship('salon', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_ar')
                            ->label('Name (Arabic)')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3),
                        Textarea::make('description_ar')
                            ->label('Description (Arabic)')
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

                Section::make('Pricing & Duration')
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('EGP')
                            ->required(),
                        TextInput::make('discount_price')
                            ->numeric()
                            ->prefix('EGP'),
                        TextInput::make('duration_minutes')
                            ->numeric()
                            ->suffix('minutes')
                            ->default(30)
                            ->required(),
                    ])->columns(3),

                Section::make('Media & Settings')
                    ->schema([
                        FileUpload::make('image')
                            ->image()
                            ->directory('services'),
                        Toggle::make('is_active')
                            ->default(true),
                        Toggle::make('is_featured'),
                        TextInput::make('sort_order')
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
                Tables\Columns\TextColumn::make('salon.name')
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
                Tables\Filters\SelectFilter::make('salon_id')
                    ->relationship('salon', 'name')
                    ->label('Salon'),
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
