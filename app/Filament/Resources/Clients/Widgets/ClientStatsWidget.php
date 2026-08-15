<?php

namespace App\Filament\Resources\Clients\Widgets;

use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalClients = Client::count();
        $newClients = Client::where('created_at', '>=', now()->startOfMonth())->count();
        $loyalClients = Client::where('isLoyal', true)->count();

        return [
            Stat::make('Total clients', $totalClients)
                ->description('Nombre total de clients')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),
            Stat::make('Nouveaux clients', $newClients)
                ->description('Nouveaux ce mois-ci')
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('success'),
            Stat::make('Clients fidèles', $loyalClients)
                ->description('Clients au statut fidèle')
                ->descriptionIcon('heroicon-o-star')
                ->color('warning'),
        ];
    }
}
