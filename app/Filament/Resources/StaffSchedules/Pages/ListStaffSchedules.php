<?php

namespace App\Filament\Resources\StaffSchedules\Pages;

use App\Filament\Resources\StaffSchedules\StaffScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffSchedules extends ListRecords
{
    protected static string $resource = StaffScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouvel horaire'),
        ];
    }
}
