<?php

namespace App\Filament\Resources\Suppliers\Widgets;

use App\Helpers\FormatHelper;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupplierStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('status', 'active')->count();
        $pendingOrders = PurchaseOrder::whereIn('status', ['pending', 'ordered'])->count();
        $totalOrdersAmount = PurchaseOrder::sum('totalAmount');

        return [
            Stat::make('TOTAL FOURNISSEURS', $totalSuppliers)
                ->description('Fournisseurs enregistrés')
                ->descriptionIcon('heroicon-o-truck')
                ->color('warning'),

            Stat::make('ACTIFS', $activeSuppliers)
                ->description('Fournisseurs actifs')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('COMMANDES EN COURS', $pendingOrders)
                ->description('En attente ou expédiées')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('info'),

            Stat::make('MONTANT TOTAL COMMANDES', FormatHelper::formatFCFA($totalOrdersAmount))
                ->description('Cumul des achats')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('danger'),
        ];
    }
}
