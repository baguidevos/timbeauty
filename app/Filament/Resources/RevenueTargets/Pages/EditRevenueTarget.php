<?php

namespace App\Filament\Resources\RevenueTargets\Pages;

use App\Filament\Resources\RevenueTargets\RevenueTargetResource;
use Filament\Resources\Pages\EditRecord;

class EditRevenueTarget extends EditRecord
{
    protected static string $resource = RevenueTargetResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
