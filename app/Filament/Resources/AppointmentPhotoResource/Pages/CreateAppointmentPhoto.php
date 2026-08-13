<?php

namespace App\Filament\Resources\AppointmentPhotoResource\Pages;

use App\Filament\Resources\AppointmentPhotoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointmentPhoto extends CreateRecord
{
    protected static string $resource = AppointmentPhotoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
