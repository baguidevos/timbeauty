<?php

namespace App\Filament\Resources\StaffAbsenceResource\Pages;

use App\Filament\Resources\StaffAbsenceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStaffAbsence extends CreateRecord
{
    protected static string $resource = StaffAbsenceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
