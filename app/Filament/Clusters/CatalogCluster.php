<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class CatalogCluster extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Catalogue & Achats';

    protected static ?string $clusterBreadcrumb = 'Catalogue & Achats';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion & Pilotage';

    protected static ?int $navigationSort = 20;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
