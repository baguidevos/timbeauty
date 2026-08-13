<?php

namespace App\Filament\Resources\StaffAbsenceResource\Pages;

use App\Filament\Resources\StaffAbsenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffAbsences extends ListRecords
{
    protected static string $resource = StaffAbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouvelle absence'),
        ];
    }
}
