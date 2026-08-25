<?php

namespace App\Filament\Resources\CashRegisters\Widgets;

use App\Helpers\FormatHelper;
use App\Models\BankDeposit;
use App\Models\CashRegister;
use App\Models\OwnerAdvance;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashRegisterStatsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int|array|null
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 2,
            'lg' => 2,
            'xl' => 4,
            '2xl' => 4,
        ];
    }

    protected function getStats(): array
    {
        $openRegister = CashRegister::where('status', 'open')->first();
        $totalSessions = CashRegister::count();
        $closedSessions = CashRegister::where('status', 'closed')->count();

        // 1. Session du jour
        if ($openRegister) {
            $theoreticalCurrent = $openRegister->getTheoreticalBalance();
            $threshold = CashRegister::getBankDepositThreshold();
            $thresholdReached = $theoreticalCurrent >= $threshold;

            $stat1Label = 'Caisse du jour (Ouverte)';
            $stat1Value = FormatHelper::formatFCFA($theoreticalCurrent);
            $stat1Desc = $thresholdReached ? '⚠️ Seuil versement atteint' : 'Solde physique théorique';
            $stat1Color = $thresholdReached ? 'warning' : 'success';
            $stat1Icon = 'heroicon-o-lock-open';
        } else {
            $lastClosed = CashRegister::where('status', 'closed')->latest('closedAt')->first();
            $stat1Label = 'Caisse du jour (Fermée)';
            $stat1Value = 'Fermée';
            $stat1Desc = $lastClosed ? 'Dernier report : '.FormatHelper::formatFCFA((float) $lastClosed->closingAmount) : 'Aucune caisse ouverte';
            $stat1Color = 'gray';
            $stat1Icon = 'heroicon-o-lock-closed';
        }

        // 2. Totaux du mois en cours
        $monthlyCashSales = Sale::where('paymentMethod', 'cash')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $monthlyBankDeposits = BankDeposit::whereMonth('depositDate', now()->month)
            ->whereYear('depositDate', now()->year)
            ->sum('amount');

        // 3. Total des dettes propriétaires en cours
        $totalOwnerDebt = OwnerAdvance::whereIn('status', ['pending', 'partially_refunded'])
            ->get()
            ->sum(fn ($a) => $a->remaining_amount);

        $stats = [
            Stat::make($stat1Label, $stat1Value)
                ->description($stat1Desc)
                ->descriptionIcon($stat1Icon)
                ->color($stat1Color),

            Stat::make('Encaissé ce mois', FormatHelper::formatFCFA($monthlyCashSales))
                ->description('Ventes en espèces')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('emerald'),

            Stat::make('Dépôts banque (mois)', FormatHelper::formatFCFA($monthlyBankDeposits))
                ->description('Versé sur compte salon')
                ->descriptionIcon('heroicon-o-building-library')
                ->color('indigo'),
        ];

        if ($totalOwnerDebt > 0) {
            $stats[] = Stat::make('Avance propriétaire', FormatHelper::formatFCFA($totalOwnerDebt))
                ->description('Dette salon à rembourser')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color('amber');
        } else {
            $stats[] = Stat::make('Sessions de caisse', (string) $totalSessions)
                ->description("{$closedSessions} sessions clôturées")
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('gray');
        }

        return $stats;
    }
}
