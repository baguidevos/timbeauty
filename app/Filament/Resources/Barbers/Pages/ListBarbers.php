<?php

namespace App\Filament\Resources\Barbers\Pages;

use App\Filament\Resources\Barbers\BarberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListBarbers extends ListRecords
{
    protected static string $resource = BarberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau coiffeur')
                ->modalWidth(Width::FiveExtraLarge),
        ];
    }
}
