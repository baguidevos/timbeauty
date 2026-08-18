<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Expenses\Pages\ListExpenses;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Services\Pages\ListServices;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Guava\Calendar\CalendarPlugin;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->spa(hasPrefetching: true)
            ->registration()
            ->maxContentWidth(Width::Full)
            ->colors([
                'primary' => Color::Amber,
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->brandName('💈 BarberShop Pro')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')

            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->collapsibleNavigationGroups()
            ->globalSearchDebounce('500ms')
            ->widgets([
                // RevenueStatsWidget::class,
                // PendingAppointmentsWidget::class,
                // ExpenseSummaryWidget::class,
                // CashRegisterStatusWidget::class,
                // TodayAppointmentsWidget::class,
                // RevenueChartWidget::class,
                // TopServicesWidget::class,
                // RecentSalesWidget::class,
                // LowStockAlertWidget::class,
                // Widgets\AccountWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE,
                fn (): View => view('filament.resources.services.components.category-badges'),
                scopes: ListServices::class,
            )
            ->renderHook(
                PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE,
                fn (): View => view('filament.resources.products.components.product-category-badges'),
                scopes: ListProducts::class,
            )
            ->renderHook(
                PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE,
                fn (): View => view('filament.resources.expenses.components.expense-category-badges'),
                scopes: ListExpenses::class,
            )
            ->renderHook(
                'panels::body.end',
                fn () => Blade::render('<link rel="stylesheet" href="/css/filament-custom.css">'),
            )

            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Principal')
                    // ->icon('heroicon-o-home')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Gestion')
                    // ->icon('heroicon-o-user-group')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Ventes')
                    // ->icon('heroicon-o-shopping-bag')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Finance')
                    // ->icon('heroicon-o-banknotes')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Système')
                    // ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
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
            ->plugins([
                CalendarPlugin::make(),
                // FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
