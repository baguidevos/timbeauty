<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Models\ExpenseCategory;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Dépenses & Charges';
    }

    public function getHeading(): Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-500 text-white shadow-md shadow-rose-500/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v-.75C2.25 4.01 3.01 3.25 3.95 3.25h16.1c.94 0 1.7.76 1.7 1.7V6h-.75a.75.75 0 0 1-.75-.75V4.5m0 0H3.75m16.5 0v11.25c0 .94-.76 1.7-1.7 1.7H3.95c-.94 0-1.7-.76-1.7-1.7V6" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Dépenses & Charges</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Suivi des dépenses et frais d\'exploitation</span>
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
                ->modalHeading('Créer une catégorie de dépense')
                ->modalWidth(Width::Large)
                ->schema([
                    TextInput::make('name')
                        ->label('Nom de la catégorie')
                        ->required()
                        ->maxLength(100),
                ])
                ->action(function (array $data): void {
                    ExpenseCategory::create($data);

                    Notification::make()
                        ->title('Catégorie créée avec succès')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Nouvelle dépense')
                ->slideOver(),
        ];
    }
}
