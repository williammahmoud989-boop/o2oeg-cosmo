<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\Reviews\Pages;
use App\Models\Review;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Icons\Heroicon;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-star';
    protected static string | \UnitEnum | null $navigationGroup = 'إدارة المحتوى';
    protected static ?string $label = 'مراجعة';
    protected static ?string $pluralLabel = 'المراجعات والتقييمات';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('تفاصيل المراجعة')
                    ->schema([
                        Select::make('salon_id')
                            ->relationship('salon', 'name')
                            ->required()
                            ->label('الصالون'),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->label('العميل'),
                        Select::make('booking_id')
                            ->relationship('booking', 'booking_code')
                            ->label('كود الحجز (اختياري)'),
                        TextInput::make('rating')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(5)
                            ->label('التقييم'),
                        Textarea::make('comment')
                            ->required()
                            ->label('التعليق')
                            ->columnSpanFull(),
                        Toggle::make('is_public')
                            ->label('عرض للعامة')
                            ->default(true),
                        Textarea::make('reply')
                            ->label('رد الصالون (للمراجعة)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('salon.name')
                    ->label('الصالون')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable(),
                TextColumn::make('rating')
                    ->label('التقييم')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '5', '4' => 'success',
                        '3' => 'warning',
                        '2', '1' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('comment')
                    ->label('التعليق')
                    ->limit(30)
                    ->searchable(),
                IconColumn::make('is_public')
                    ->label('عام')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y/m/d')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}






