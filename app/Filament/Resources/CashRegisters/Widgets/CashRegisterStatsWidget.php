<?php

namespace App\Filament\Resources\CashRegisters\Widgets;

use App\Helpers\FormatHelper;
use App\Models\CashRegister;
use App\Models\Expense;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashRegisterStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $openRegister = CashRegister::where('status', 'open')->first();
        $totalSessions = CashRegister::count();
        $closedSessions = CashRegister::where('status', 'closed')->count();

        // Si une caisse est ouverte aujourd'hui, calculer ses chiffres
        if ($openRegister) {
            $cashSales = Sale::where('cashRegisterId', $openRegister->id)
                ->where('paymentMethod', 'cash')
                ->sum('total');

            $cashExpenses = Expense::where('cashRegisterId', $openRegister->id)
                ->where('paymentMethod', 'cash')
                ->sum('amount');

            $theoreticalCurrent = (float) $openRegister->openingAmount + $cashSales - $cashExpenses;

            $statusValue = 'OUVERTE';
            $statusDesc = 'Solde actuel : '.FormatHelper::formatFCFA($theoreticalCurrent);
            $statusColor = 'success';
            $statusIcon = 'heroicon-o-lock-open';
        } else {
            $lastClosed = CashRegister::where('status', 'closed')->latest('closedAt')->first();
            $statusValue = 'FERMÉE';
            $statusDesc = $lastClosed ? 'Dernier report : '.FormatHelper::formatFCFA((float) $lastClosed->closingAmount) : 'Aucune caisse ouverte';
            $statusColor = 'gray';
            $statusIcon = 'heroicon-o-lock-closed';
        }

        // Totaux du mois en cours
        $monthlyCashSales = Sale::where('paymentMethod', 'cash')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $monthlyCashExpenses = Expense::where('paymentMethod', 'cash')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return [
            Stat::make('SESSION DU JOUR', $statusValue)
                ->description($statusDesc)
                ->descriptionIcon($statusIcon)
                ->color($statusColor),

            Stat::make('ENCAISSÉ CE MOIS', FormatHelper::formatFCFA($monthlyCashSales))
                ->description('Ventes et prestations en espèces')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('emerald'),

            Stat::make('DÉPENSES CE MOIS', FormatHelper::formatFCFA($monthlyCashExpenses))
                ->description('Décaissements en espèces')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('rose'),

            Stat::make('HISTORIQUE DES SESSIONS', $totalSessions)
                ->description($closedSessions.' sessions clôturées')
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('amber'),
        ];
    }
}
