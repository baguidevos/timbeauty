<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class FinanceCluster extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Trésorerie & Finances';

    protected static ?string $clusterBreadcrumb = 'Trésorerie & Finances';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion & Pilotage';

    protected static ?int $navigationSort = 10;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
