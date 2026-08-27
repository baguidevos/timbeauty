<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Statistics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Système & Pilotage';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Statistiques';

    protected string $view = 'filament.pages.statistics';

    protected static ?string $title = 'Statistiques';
}
