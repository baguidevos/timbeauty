<?php

namespace App\Filament\Resources\OwnerAdvances\Pages;

use App\Filament\Resources\OwnerAdvances\OwnerAdvanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOwnerAdvances extends ListRecords
{
    protected static string $resource = OwnerAdvanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel apport propriétaire')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['createdBy'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
