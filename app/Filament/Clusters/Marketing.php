<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class Marketing extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Marketing & Fidélité';

    protected static ?string $clusterBreadcrumb = 'Marketing & Fidélité';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 35;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
