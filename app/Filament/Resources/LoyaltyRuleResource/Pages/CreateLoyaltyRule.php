<?php

namespace App\Filament\Resources\LoyaltyRuleResource\Pages;

use App\Filament\Resources\LoyaltyRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLoyaltyRule extends CreateRecord
{
    protected static string $resource = LoyaltyRuleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
