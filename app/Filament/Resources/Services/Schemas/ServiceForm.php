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
                            ->label('Taux de commission (%)')
                            ->validationMessages([
                                'required' => 'Le taux de commission est requis, 0 par defaut',
                                'numeric' => 'Le taux de commission doit être un nombre',
                                'step' => 'Le taux de commission doit être un nombre avec 2 décimales',
                                'min' => 'Le taux de commission doit être supérieur ou égal à 0',
                                'max' => 'Le taux de commission doit être inférieur ou égal à 100',
                            ])
                            ->required()
                            ->default(0)
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
