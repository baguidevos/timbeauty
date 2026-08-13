<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Statistics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static string|\UnitEnum|null $navigationGroup = 'Principal';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Statistiques';

    protected string $view = 'filament.pages.statistics';

    protected static ?string $title = 'Statistiques';
}
