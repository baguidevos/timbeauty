<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class GreetingWidget extends Widget
{
    protected string $view = 'filament.widgets.greeting-widget';

    protected int|string|array $columnSpan = 'full';

    public ?string $greeting = null;

    public ?string $dayLabel = null;

    public ?string $timeLabel = null;

    public ?string $updatedAt = null;

    public function mount(): void
    {
        $this->refreshData();
    }

    public function refreshData(): void
    {
        $now = Carbon::now();
        $hour = $now->hour;

        if ($hour < 12) {
            $this->greeting = 'Bonjour';
        } elseif ($hour < 18) {
            $this->greeting = 'Bon après-midi';
        } else {
            $this->greeting = 'Bonsoir';
        }

        $days = [
            'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche',
        ];

        $months = [
            'January' => 'janvier', 'February' => 'février', 'March' => 'mars',
            'April' => 'avril', 'May' => 'mai', 'June' => 'juin',
            'July' => 'juillet', 'August' => 'août', 'September' => 'septembre',
            'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre',
        ];

        $dayName = $days[$now->format('l')] ?? $now->format('l');
        $monthName = $months[$now->format('F')] ?? $now->format('F');

        $this->dayLabel = "{$dayName} {$now->format('j')} {$monthName} {$now->format('Y')}";
        $this->timeLabel = $now->format('H:i');
        $this->updatedAt = $now->format('H:i:s');
    }
}
