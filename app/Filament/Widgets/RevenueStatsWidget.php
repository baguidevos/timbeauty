<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatHelper;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $todayRevenue = Sale::where('status', 'completed')->whereDate('created_at', $today)->sum('total');
        $monthlyRevenue = Sale::where('status', 'completed')->whereDate('created_at', '>=', $monthStart)->sum('total');
        $todayAppointments = Appointment::where('date', $today)->count();
        $totalClients = Client::count();
        $newClientsThisMonth = Client::whereDate('firstVisitDate', '>=', $monthStart)->count();

        return [
            Stat::make('Revenu du jour', FormatHelper::formatFCFA($todayRevenue))
                ->description('Ventes aujourd\'hui')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->chart($this->getTodayChart()),
            Stat::make('Revenu du mois', FormatHelper::formatFCFA($monthlyRevenue))
                ->description('Ventes ce mois')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('primary')
                ->chart($this->getMonthlyChart()),
            Stat::make('RDV aujourd\'hui', $todayAppointments)
                ->description('Rendez-vous du jour')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info'),
            Stat::make('Total clients', $totalClients)
                ->description("{$newClientsThisMonth} nouveau(x) ce mois")
                ->descriptionIcon('heroicon-o-users')
                ->color('warning'),
        ];
    }

    private function getTodayChart(): array
    {
        return Sale::where('status', 'completed')
            ->whereDate('created_at', now()->toDateString())
            ->selectRaw("strftime('%H', created_at) as hour, SUM(total) as total")
            ->groupByRaw("strftime('%H', created_at)")
            ->orderBy('hour')
            ->pluck('total')
            ->take(7)
            ->toArray();
    }

    private function getMonthlyChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $data[] = (float) Sale::where('status', 'completed')->whereDate('created_at', $date)->sum('total');
        }

        return $data;
    }
}
