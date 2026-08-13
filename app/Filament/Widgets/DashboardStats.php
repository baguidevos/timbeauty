<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatHelper;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->toDateString();

        $todayAppointments = Appointment::where('date', $today)->count();
        $todaySales = Sale::whereDate('created_at', $today)->sum('total');
        $activeClients = Client::count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();

        return [
            Stat::make('Rendez-vous aujourd\'hui', $todayAppointments)
                ->description('RDV du jour')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('primary'),
            Stat::make('Chiffre d\'affaires du jour', FormatHelper::formatFCFA($todaySales))
                ->description('Ventes du jour')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Clients', $activeClients)
                ->description('Total clients')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),
            Stat::make('RDV en attente', $pendingAppointments)
                ->description('À confirmer')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
        ];
    }
}
