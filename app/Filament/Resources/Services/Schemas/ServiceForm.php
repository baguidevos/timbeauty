<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations de la prestation')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->columnSpanFull()
                            ->label('Nom de la prestation')
                            ->hint('Exemple : Coupe Homme')
                            ->required()
                            ->maxLength(100),
                        Textarea::make('description')
                            ->label('Description de la prestation')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->label('Prix (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('duration')
                            ->label('Durée (minutes)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix('min'),
                        Select::make('categoryId')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nom de la catégorie')
                                    ->required()
                                    ->maxLength(100),
                                Textarea::make('description')
                                    ->label('Description')
                                    ->maxLength(500),
                            ]),

                        TextInput::make('commissionRate')
                            ->default(0)
                            ->label('Taux de commission (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        ToggleButtons::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                            ])
                            ->colors([
                                'active' => 'success',
                                'inactive' => 'danger',
                            ])
                            ->icons([
                                'active' => 'heroicon-o-check-circle',
                                'inactive' => 'heroicon-o-x-circle',
                            ])
                            ->default('active')
                            ->inline()
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
