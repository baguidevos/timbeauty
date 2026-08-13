<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord';

    protected static ?int $navigationSort = -2;

    public function getHeading(): string
    {
        return 'Tableau de bord';
    }

    public function getSubheading(): ?string
    {
        $days = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
        ];

        $months = [
            'January' => 'janvier',
            'February' => 'février',
            'March' => 'mars',
            'April' => 'avril',
            'May' => 'mai',
            'June' => 'juin',
            'July' => 'juillet',
            'August' => 'août',
            'September' => 'septembre',
            'October' => 'octobre',
            'November' => 'novembre',
            'December' => 'décembre',
        ];

        $now = now();
        $dayName = $days[$now->format('l')] ?? $now->format('l');
        $monthName = $months[$now->format('F')] ?? $now->format('F');

        return "{$dayName} {$now->format('j')} {$monthName} {$now->format('Y')}";
    }
}
