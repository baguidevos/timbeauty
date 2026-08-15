<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function getTabs(): array
    {
        $lowStockCount = Product::lowStock()->count();

        return [
            'all' => Tab::make('Tous les produits')
                ->badge(Product::count()),
            'low_stock' => Tab::make('Stock bas')
                ->icon('heroicon-m-exclamation-triangle')
                ->modifyQueryUsing(fn ($query) => $query->lowStock())
                ->badge($lowStockCount)
                ->badgeColor($lowStockCount > 0 ? 'danger' : 'gray'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Produits & Inventaire';
    }

    public function getHeading(): Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Produits & Inventaire</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Gestion des produits et suivi du stock</span>
                </div>
            </div>
        ');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createCategory')
                ->label('Nouvelle catégorie')
                ->icon('heroicon-o-folder-plus')
                ->color('gray')
                ->modalHeading('Créer une catégorie de produit')
                ->modalWidth(Width::Large)
                ->schema([
                    TextInput::make('name')
                        ->label('Nom de la catégorie')
                        ->required()
                        ->maxLength(100),
                    Textarea::make('description')
                        ->label('Description')
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    ProductCategory::create($data);

                    Notification::make()
                        ->title('Catégorie créée avec succès')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Nouveau produit')
                ->slideOver(),
        ];
    }
}
