<?php

namespace App\Filament\Resources\BarberResource\Pages;

use App\Filament\Resources\BarberResource;
use Filament\Resources\Pages\EditRecord;

class EditBarber extends EditRecord
{
    protected static string $resource = BarberResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
