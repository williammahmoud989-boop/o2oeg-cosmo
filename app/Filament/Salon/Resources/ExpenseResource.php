<?php

namespace App\Filament\Salon\Resources;

use App\Filament\Salon\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-receipt-refund';

    protected static string | \UnitEnum | null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('تفاصيل المصروف')
                ->schema([
                    Select::make('category_id')
                        ->label('التصنيف')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('amount')
                        ->label('المبلغ')
                        ->numeric()
                        ->prefix('EGP')
                        ->required(),
                    DatePicker::make('expense_date')
                        ->label('تاريخ الصرف')
                        ->default(now())
                        ->required(),
                    Textarea::make('description')
                        ->label('الوصف / ملاحظات')
                        ->rows(3)
                        ->columnSpanFull(),
                    FileUpload::make('receipt_path')
                        ->label('إيصال المصروف (صورة)')
                        ->image()
                        ->directory('expenses/receipts')
                        ->columnSpanFull(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('التصنيف')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50),
                Tables\Columns\ImageColumn::make('receipt_path')
                    ->label('الإيصال')
                    ->circular(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name'),
                Tables\Filters\Filter::make('expense_date')
                    ->form([
                        DatePicker::make('from')->label('من'),
                        DatePicker::make('until')->label('إلى'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($query) => $query->whereDate('expense_date', '>=', $data['from']))
                        ->when($data['until'], fn ($query) => $query->whereDate('expense_date', '<=', $data['until']))
                    ),
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
