<?php

namespace App\Filament\Resources\LoyaltyRules\Pages;

use App\Filament\Resources\LoyaltyRules\LoyaltyRuleResource;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyRule extends EditRecord
{
    protected static string $resource = LoyaltyRuleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
