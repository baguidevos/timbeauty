<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pos')
                ->label('Ouvrir la Caisse (POS)')
                ->icon('heroicon-o-shopping-cart')
                ->color('amber')
                ->url(fn (): string => route('filament.admin.pages.pos')),
            Actions\CreateAction::make()->label('Nouvelle vente manuelle'),
        ];
    }
}
