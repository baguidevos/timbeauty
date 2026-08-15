<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Models\ServiceCategory;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createCategory')
                ->label('Nouvelle catégorie')
                ->icon('heroicon-o-folder-plus')
                ->color('gray')
                ->modalHeading('Créer une catégorie de prestation')
                ->modalWidth(Width::Large)
                ->schema([
                    TextInput::make('name')
                        ->label('Nom de la catégorie')
                        ->required()
                        ->maxLength(100),
                    Textarea::make('description')
                        ->label('Description')
                        ->maxLength(500),
                    TextInput::make('icon')
                        ->label('Icône')
                        ->maxLength(50),
                    TextInput::make('order')
                        ->label('Ordre d\'affichage')
                        ->numeric()
                        ->default(0),
                ])
                ->action(function (array $data): void {
                    ServiceCategory::create($data);

                    Notification::make()
                        ->title('Catégorie créée avec succès')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Nouvelle prestation')
                ->icon('heroicon-o-plus')
                ->modelLabel('prestation')
                ->pluralModelLabel('prestations')
                ->modalWidth(Width::MaxContent),
        ];
    }
}
