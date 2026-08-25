<?php

namespace App\Filament\Resources\BankDeposits\Pages;

use App\Filament\Resources\BankDeposits\BankDepositResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBankDeposits extends ListRecords
{
    protected static string $resource = BankDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau versement banque')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['createdBy'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
