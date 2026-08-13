<?php

namespace App\Filament\Resources\Barbers\Pages;

use App\Filament\Resources\Barbers\BarberResource;
use Filament\Resources\Pages\EditRecord;

class EditBarber extends EditRecord
{
    protected static string $resource = BarberResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
