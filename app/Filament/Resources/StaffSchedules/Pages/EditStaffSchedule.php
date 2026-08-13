<?php

namespace App\Filament\Resources\StaffSchedules\Pages;

use App\Filament\Resources\StaffSchedules\StaffScheduleResource;
use Filament\Resources\Pages\EditRecord;

class EditStaffSchedule extends EditRecord
{
    protected static string $resource = StaffScheduleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
