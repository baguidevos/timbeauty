<?php

namespace App\Filament\Resources\RevenueTargets\Pages;

use App\Filament\Resources\RevenueTargets\RevenueTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRevenueTargets extends ListRecords
{
    protected static string $resource = RevenueTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouvel objectif'),
        ];
    }
}
