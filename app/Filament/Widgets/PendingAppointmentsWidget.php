<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->toDateString();
        $pendingCount = Appointment::where('date', $today)
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('RDV en attente', $pendingCount)
                ->description('À confirmer aujourd\'hui')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->extraAttributes([
                    'class' => $pendingCount > 0 ? 'amber-pulse' : '',
                ]),
        ];
    }
}
