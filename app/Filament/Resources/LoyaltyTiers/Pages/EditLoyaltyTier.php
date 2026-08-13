<?php

namespace App\Filament\Resources\LoyaltyTiers\Pages;

use App\Filament\Resources\LoyaltyTiers\LoyaltyTierResource;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyTier extends EditRecord
{
    protected static string $resource = LoyaltyTierResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
