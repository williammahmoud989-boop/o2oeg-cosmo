<?php

namespace App\Filament\Salon\Resources;

use App\Filament\Salon\Resources\PackageResource\Pages;
use App\Models\Package;
use App\Models\Service;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-gift';

    protected static string | \UnitEnum | null $navigationGroup = 'Marketing & Sales';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات الباقة')
                ->schema([
                    TextInput::make('name')
                        ->label('اسم الباقة')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('price')
                        ->label('سعر الباقة')
                        ->numeric()
                        ->prefix('EGP')
                        ->required(),
                    TextInput::make('validity_days')
                        ->label('الصلاحية (بالأيام)')
                        ->numeric()
                        ->default(30)
                        ->required(),
                    Toggle::make('is_active')
                        ->label('مفعلة')
                        ->default(true),
                    Textarea::make('description')
                        ->label('وصف الباقة')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('خدمات الباقة')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('service_id')
                                ->label('الخدمة')
                                ->options(Service::pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            TextInput::make('quantity')
                                ->label('العدد المتاح في الباقة')
                                ->numeric()
                                ->default(1)
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('إضافة خدمة للباقة'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الباقة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('validity_days')
                    ->label('الصلاحية (أيام)')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
