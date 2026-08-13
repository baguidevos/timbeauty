<?php

namespace App\Filament\Resources\LoyaltyRuleResource\Pages;

use App\Filament\Resources\LoyaltyRuleResource;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyRule extends EditRecord
{
    protected static string $resource = LoyaltyRuleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
