<?php

namespace App\Filament\Resources\AppointmentPhotoResource\Pages;

use App\Filament\Resources\AppointmentPhotoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppointmentPhotos extends ListRecords
{
    protected static string $resource = AppointmentPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouvelle photo'),
        ];
    }
}
