<?php

namespace App\Filament\Salon\Resources;

use App\Filament\Salon\Resources\ExpenseCategoryResource\Pages;
use App\Models\ExpenseCategory;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';

    protected static string | \UnitEnum | null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('تصنيف المصروفات')
                ->schema([
                    TextInput::make('name')
                        ->label('اسم التصنيف')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('الوصف')
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50),
                Tables\Columns\TextColumn::make('expenses_count')
                    ->label('عدد المصروفات')
                    ->counts('expenses'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListExpenseCategories::route('/'),
        ];
    }
}
