<?php

namespace App\Filament\Resources\StaffScheduleResource\Pages;

use App\Filament\Resources\StaffScheduleResource;
use Filament\Resources\Pages\EditRecord;

class EditStaffSchedule extends EditRecord
{
    protected static string $resource = StaffScheduleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
