<?php

namespace App\Providers\Filament;

use App\Providers\ModuleServiceProvider;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\CustomAccountWidget::class,
            ])
            ->navigationGroups(array_merge(
                // Auto-discover navigation groups from modules
                array_map(
                    fn ($module) => NavigationGroup::make()->label($module->navigationGroup()),
                    ModuleServiceProvider::discoverModules()
                ),
                // Core navigation groups
                [
                    NavigationGroup::make()->label('Content'),
                    NavigationGroup::make()->label('Marketing'),
                    NavigationGroup::make()->label('Security'),
                    NavigationGroup::make()->label('System'),
                ]
            ))
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Clear Cache')
                    ->url('/admin/clear-cache-redirect')
                    ->icon('heroicon-o-trash')
                    ->group('System')
                    ->sort(99)
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        // Auto-discover Filament resources from all registered modules
        foreach (ModuleServiceProvider::getFilamentResourcePaths() as $resourcePath) {
            $panel->discoverResources(
                in: $resourcePath['path'],
                for: $resourcePath['namespace'],
            );
        }

        // Auto-discover Filament pages from all registered modules
        foreach (ModuleServiceProvider::discoverModules() as $module) {
            $reflection = new \ReflectionClass($module);
            $moduleDir = dirname($reflection->getFileName());
            $pagePath = $moduleDir . '/Filament/Pages';
            $pageNamespace = $reflection->getNamespaceName() . '\\Filament\\Pages';

            if (is_dir($pagePath)) {
                $panel->discoverPages(
                    in: $pagePath,
                    for: $pageNamespace,
                );
            }
        }

        // Auto-discover Filament widgets from all registered modules
        foreach (ModuleServiceProvider::discoverModules() as $module) {
            $reflection = new \ReflectionClass($module);
            $moduleDir = dirname($reflection->getFileName());
            $widgetPath = $moduleDir . '/Filament/Widgets';
            $widgetNamespace = $reflection->getNamespaceName() . '\\Filament\\Widgets';

            if (is_dir($widgetPath)) {
                $panel->discoverWidgets(
                    in: $widgetPath,
                    for: $widgetNamespace,
                );
            }
        }

        return $panel;
    }
}

