<?php

namespace App\Filament\Resources\AppointmentPhotos\Pages;

use App\Filament\Resources\AppointmentPhotos\AppointmentPhotoResource;
use Filament\Resources\Pages\EditRecord;

class EditAppointmentPhoto extends EditRecord
{
    protected static string $resource = AppointmentPhotoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
