<?php

namespace App\Filament\Resources\LoyaltyTierResource\Pages;

use App\Filament\Resources\LoyaltyTierResource;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyTier extends EditRecord
{
    protected static string $resource = LoyaltyTierResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
