<?php

namespace App\Filament\Resources\StaffAbsenceResource\Pages;

use App\Filament\Resources\StaffAbsenceResource;
use Filament\Resources\Pages\EditRecord;

class EditStaffAbsence extends EditRecord
{
    protected static string $resource = StaffAbsenceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
