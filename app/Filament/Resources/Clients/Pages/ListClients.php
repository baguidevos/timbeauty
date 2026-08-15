<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Widgets\ClientStatsWidget;
use App\Models\Client;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Clients';
    }

    public function getHeading(): Htmlable
    {
        $count = Client::count();
        $clientText = $count === 1 ? '1 client au total' : "{$count} clients au total";

        return new HtmlString("
            <div class=\"flex items-center gap-3\">
                <div class=\"flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20\">
                    <svg class=\"h-6 w-6\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z\" />
                    </svg>
                </div>
                <div class=\"flex flex-col text-left\">
                    <span class=\"text-xl font-bold tracking-tight text-gray-950 dark:text-white\">Clients</span>
                    <span class=\"text-xs font-normal text-gray-500 dark:text-gray-400\">{$clientText} — gérez vos fidèles et vos visites</span>
                </div>
            </div>
        ");
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau client')
                ->modalWidth(Width::FiveExtraLarge),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ClientStatsWidget::class,
        ];
    }
}
