<?php

namespace App\Filament\Resources\LoyaltyPointTransactions\Pages;

use App\Filament\Resources\LoyaltyPointTransactions\LoyaltyPointTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyPointTransactions extends ListRecords
{
    protected static string $resource = LoyaltyPointTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouvelle transaction'),
        ];
    }
}
