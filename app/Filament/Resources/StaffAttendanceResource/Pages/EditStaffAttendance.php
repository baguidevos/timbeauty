<?php

namespace App\Filament\Resources\StaffAttendanceResource\Pages;

use App\Filament\Resources\StaffAttendanceResource;
use Filament\Resources\Pages\EditRecord;

class EditStaffAttendance extends EditRecord
{
    protected static string $resource = StaffAttendanceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
