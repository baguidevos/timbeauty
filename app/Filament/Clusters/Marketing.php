<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class Marketing extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Marketing & Fidélité';

    protected static ?string $clusterBreadcrumb = 'Marketing & Fidélité';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion & Pilotage';

    protected static ?int $navigationSort = 40;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
