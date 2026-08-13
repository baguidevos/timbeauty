<?php

namespace App\Filament\Resources\StaffAbsences\Pages;

use App\Filament\Resources\StaffAbsences\StaffAbsenceResource;
use Filament\Resources\Pages\EditRecord;

class EditStaffAbsence extends EditRecord
{
    protected static string $resource = StaffAbsenceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
