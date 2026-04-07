<?php

namespace App\Filament\Admin\Resources\Reviews\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('salon_id')
                    ->relationship('salon', 'name')
                    ->required(),
                Select::make('booking_id')
                    ->relationship('booking', 'id')
                    ->required(),
                TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(5),
                Textarea::make('comment')
                    ->columnSpanFull(),
                Textarea::make('reply')
                    ->columnSpanFull(),
                DateTimePicker::make('replied_at'),
                Toggle::make('is_public')
                    ->required(),
            ]);
    }
}
