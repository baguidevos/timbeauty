<?php

namespace App\Filament\Resources\StaffAttendances\Pages;

use App\Filament\Resources\StaffAttendances\StaffAttendanceResource;
use App\Filament\Resources\StaffAttendances\Widgets\StaffAttendanceWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListStaffAttendances extends ListRecords
{
    protected static string $resource = StaffAttendanceResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Présences & Pointage';
    }

    public function getHeading(): Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Présences & Pointage</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Pointage en direct des arrivées, départs et suivi des heures travaillées</span>
                </div>
            </div>
        ');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle présence')
                ->slideOver(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StaffAttendanceWidget::class,
        ];
    }
}
