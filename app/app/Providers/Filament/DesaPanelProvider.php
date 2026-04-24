<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem;

class DesaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $appProfile = appProfile();
        $brandName = 'DESA - ' . ($appProfile->region_name ?? 'Manajemen');

        return $panel
            ->id('desa')
            ->path('desa/admin')
            ->login()
            ->colors([
                'primary' => Color::Sky,
            ])
            ->font('Outfit')
            ->navigationItems([
                NavigationItem::make('Beranda Dashboard')
                    ->url(fn(): string => route('desa.dashboard'))
                    ->icon('heroicon-o-home')
                    ->sort(-1),
            ])
            ->resources([
                \App\Filament\Admin\Resources\DokumenPencairanDesaResource::class,
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
