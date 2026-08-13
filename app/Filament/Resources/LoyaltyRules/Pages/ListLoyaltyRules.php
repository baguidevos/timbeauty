<?php

namespace App\Filament\Resources\LoyaltyRules\Pages;

use App\Filament\Resources\LoyaltyRules\LoyaltyRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyRules extends ListRecords
{
    protected static string $resource = LoyaltyRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouvelle règle'),
        ];
    }
}
