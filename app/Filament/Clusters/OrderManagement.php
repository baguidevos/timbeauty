<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class OrderManagement extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Gestion des Fournisseurs';

    protected static ?string $clusterBreadcrumb = 'Gestion de Commande';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 30;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
