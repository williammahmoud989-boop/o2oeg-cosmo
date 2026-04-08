<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SalonPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('salon')
            ->path('salon')
            ->login()
            ->registration()
            ->tenant(\App\Models\Salon::class)
            ->tenantRegistration(\App\Filament\Salon\Pages\Tenancy\RegisterSalon::class)
            ->tenantProfile(\App\Filament\Salon\Pages\Tenancy\EditSalonProfile::class)
            ->colors([
                'primary' => Color::Orange,
                'secondary' => Color::Indigo,
                'gray' => Color::Slate,
            ])
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->brandName('O2O EG | Salon Hub')
            ->brandLogo(asset('images/logo-new.png'))
            ->brandLogoHeight('3rem')
            ->font('Cairo')
            ->discoverResources(in: app_path('Filament/Salon/Resources'), for: 'App\Filament\Salon\Resources')
            ->discoverPages(in: app_path('Filament/Salon/Pages'), for: 'App\Filament\Salon\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Salon/Widgets'), for: 'App\Filament\Salon\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
