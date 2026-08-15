<?php

namespace App\Filament\Resources\PurchaseOrders\Widgets;

use App\Helpers\FormatHelper;
use App\Models\PurchaseOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PurchaseOrderStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalOrders = PurchaseOrder::count();
        $inProgressOrders = PurchaseOrder::whereIn('status', ['pending', 'ordered'])->count();
        $receivedOrders = PurchaseOrder::where('status', 'received')->count();
        $totalPaidAmount = PurchaseOrder::sum('paidAmount');

        return [
            Stat::make('TOTAL COMMANDES', $totalOrders)
                ->description('Commandes passées')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('EN COURS', $inProgressOrders)
                ->description('En attente ou expédiées')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),

            Stat::make('REÇUES', $receivedOrders)
                ->description('Commandes réceptionnées')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('TOTAL PAYÉ', FormatHelper::formatFCFA($totalPaidAmount))
                ->description('Montant total déboursé')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('danger'),
        ];
    }
}
