<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatHelper;
use App\Models\CashRegister;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashRegisterStatusWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $openRegister = CashRegister::where('status', 'open')->latest()->first();

        if (! $openRegister) {
            return [
                Stat::make('Caisse', 'Fermée')
                    ->description('Aucune caisse ouverte')
                    ->descriptionIcon('heroicon-o-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        $currentBalance = $openRegister->getTheoreticalBalance();

        $openedBy = $openRegister->opener?->name ?? '—';

        return [
            Stat::make('Caisse ouverte', FormatHelper::formatFCFA($openRegister->openingAmount))
                ->description("Ouverte par {$openedBy}")
                ->descriptionIcon('heroicon-o-lock-open')
                ->color('success'),
            Stat::make('Solde actuel', FormatHelper::formatFCFA($currentBalance))
                ->description('Recettes - Dépenses')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),
            Stat::make('Ouverte à', $openRegister->openedAt?->format('H:i') ?? '—')
                ->description($openRegister->openedAt?->format('d/m/Y') ?? '—')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
        ];
    }
}
