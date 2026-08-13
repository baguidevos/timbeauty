<?php

namespace App\Filament\Resources\StaffAttendances\Pages;

use App\Filament\Resources\StaffAttendances\StaffAttendanceResource;
use Filament\Resources\Pages\EditRecord;

class EditStaffAttendance extends EditRecord
{
    protected static string $resource = StaffAttendanceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
