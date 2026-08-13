<?php

namespace App\Filament\Resources\LoyaltyPointTransactions\Pages;

use App\Filament\Resources\LoyaltyPointTransactions\LoyaltyPointTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLoyaltyPointTransaction extends CreateRecord
{
    protected static string $resource = LoyaltyPointTransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
