<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class HumanResources extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Équipe & RH';

    protected static ?string $clusterBreadcrumb = 'Équipe & RH';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion & Pilotage';

    protected static ?int $navigationSort = 30;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
