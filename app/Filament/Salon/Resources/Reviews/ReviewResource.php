<?php

namespace App\Filament\Salon\Resources\Reviews;

use App\Filament\Salon\Resources\Reviews\Pages\ManageReviews;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Section::make('Review Details')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->required(),
                        Select::make('booking_id')
                            ->relationship('booking', 'id')
                            ->disabled()
                            ->required(),
                        TextInput::make('rating')
                            ->disabled()
                            ->required()
                            ->numeric()
                            ->default(5),
                    ])->columns(3),
                
                Section::make('User Feedback')
                    ->schema([
                        Textarea::make('comment')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make('Salon Response')
                    ->schema([
                        Textarea::make('reply')
                            ->label('Your Reply')
                            ->columnSpanFull(),
                        Toggle::make('is_public')
                            ->label('Visible to Public')
                            ->default(true),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('salon.name')
                    ->label('Salon'),
                TextEntry::make('booking.id')
                    ->label('Booking'),
                TextEntry::make('rating')
                    ->numeric(),
                TextEntry::make('comment')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('reply')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('replied_at')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('is_public')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Review $record): bool => $record->trashed()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable(),
                TextColumn::make('rating')
                    ->icon('heroicon-s-star')
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('comment')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('replied_at')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Replied' : 'Pending')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                IconColumn::make('is_public')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReviews::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
