<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use App\Filament\Resources\Suppliers\Widgets\SupplierStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Gestion des Fournisseurs';
    }

    public function getHeading(): Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.948c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V13.5" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Gestion des Fournisseurs</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Fournisseurs et commandes d\'achat</span>
                </div>
            </div>
        ');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau fournisseur')
                ->slideOver(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SupplierStatsWidget::class,
        ];
    }
}
