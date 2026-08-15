<?php

namespace App\Filament\Resources\Payrolls\Widgets;

use App\Helpers\FormatHelper;
use App\Models\Payroll;
use App\Models\SalaryPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayrollStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $currentMonth = (int) now()->format('m');
        $currentYear = (int) now()->format('Y');

        $payrolls = Payroll::with('salaryPayments')
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get();

        $totalFixed = $payrolls->sum('fixedSalary');
        $totalCommissions = $payrolls->sum('commissions');
        $totalAdvances = $payrolls->sum('advances');
        $totalNet = $payrolls->sum('netSalary');

        $totalPaid = SalaryPayment::whereHas('payroll', function ($query) use ($currentMonth, $currentYear) {
            $query->where('month', $currentMonth)->where('year', $currentYear);
        })->sum('amount');

        $remaining = max(0, $totalNet - $totalPaid);
        $payRatio = $totalNet > 0 ? round(($totalPaid / $totalNet) * 100) : 0;

        return [
            Stat::make('SALAIRES FIXES (MOIS EN COURS)', FormatHelper::formatFCFA($totalFixed))
                ->description('Masse salariale fixe de base')
                ->descriptionIcon('heroicon-o-wallet')
                ->color('info'),

            Stat::make('COMMISSIONS GÉNÉRÉES', FormatHelper::formatFCFA($totalCommissions))
                ->description('Commissions sur prestations du mois')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('TOTAL AVANCES DU MOIS', FormatHelper::formatFCFA($totalAdvances))
                ->description('Acomptes versés au personnel')
                ->descriptionIcon('heroicon-o-hand-raised')
                ->color('warning'),

            Stat::make('MASSE SALARIALE NETTE', FormatHelper::formatFCFA($totalNet))
                ->description('Payé : '.FormatHelper::formatFCFA($totalPaid).' • Reste : '.FormatHelper::formatFCFA($remaining)." ({$payRatio}%)")
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($remaining === 0.0 && $totalNet > 0 ? 'success' : 'primary'),
        ];
    }
}
