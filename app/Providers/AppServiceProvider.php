<?php

namespace App\Providers;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;
use App\Models\Booking;
use App\Observers\BookingObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\Communication\WhatsAppProviderInterface::class, function ($app) {
            $provider = config('services.whatsapp.provider', 'log');

            return match ($provider) {
                'twilio' => new \App\Services\Communication\Providers\TwilioProvider(),
                'ultramsg' => new \App\Services\Communication\Providers\UltraMsgProvider(),
                default => new \App\Services\Communication\Providers\LogProvider(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! Stringable::hasMacro('doesntContain')) {
            Stringable::macro('doesntContain', function ($needles) {
                return ! \Illuminate\Support\Str::contains($this->value, $needles);
            });
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Booking::observe(BookingObserver::class);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar','en'])
                ->labels([
                    'ar' => 'العربية',
                    'en' => 'English',
                ]);
        });

        Field::configureUsing(function (Field $field): void {
            $field->translateLabel();
        });

        Column::configureUsing(function (Column $column): void {
            $column->translateLabel();
        });

        BaseFilter::configureUsing(function (BaseFilter $filter): void {
            $filter->translateLabel();
        });
    }
}
