<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatHelper;
use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExpenseSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $today = now()->toDateString();
        $todayExpenses = Expense::where('date', $today)->sum('amount');
        $monthStart = now()->startOfMonth()->toDateString();
        $monthlyExpenses = Expense::where('date', '>=', $monthStart)->sum('amount');

        return [
            Stat::make('Dépenses du jour', FormatHelper::formatFCFA($todayExpenses))
                ->description('Total dépenses aujourd\'hui')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('danger'),
            Stat::make('Dépenses du mois', FormatHelper::formatFCFA($monthlyExpenses))
                ->description('Total dépenses ce mois')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('danger'),
        ];
    }
}
