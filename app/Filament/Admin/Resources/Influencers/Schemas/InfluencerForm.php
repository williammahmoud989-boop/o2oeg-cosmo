<?php

namespace App\Filament\Admin\Resources\Influencers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class InfluencerForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('referral_code')
                    ->required(),
                TextInput::make('commission_rate')
                    ->required()
                    ->numeric()
                    ->default(10),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('instagram_handle'),
                Textarea::make('payment_info')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
