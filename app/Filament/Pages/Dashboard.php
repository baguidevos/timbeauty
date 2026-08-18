<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Livewire\Attributes\Url;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord';

    protected static ?int $navigationSort = -2;

    #[Url(as: 'tab')]
    public string $activeTab = 'daily';

    public function getHeading(): string
    {
        return 'Tableau de bord';
    }
}
