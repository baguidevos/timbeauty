<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Override;

class Reports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Principal';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Rapports';

    protected string $view = 'filament.pages.reports';

    protected static ?string $title = 'Rapports';

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
