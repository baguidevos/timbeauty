<?php

namespace App\Filament\Resources\AppointmentPhotos\Pages;

use App\Filament\Resources\AppointmentPhotos\AppointmentPhotoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointmentPhoto extends CreateRecord
{
    protected static string $resource = AppointmentPhotoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
