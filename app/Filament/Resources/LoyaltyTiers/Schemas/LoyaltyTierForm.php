<?php

namespace App\Filament\Resources\LoyaltyTiers\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoyaltyTierForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations du niveau')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('minPoints')
                            ->label('Points minimum')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('pointsPerFCFA')
                            ->label('Points par FCFA')
                            ->numeric()
                            ->step(0.0001)
                            ->required()
                            ->minValue(0),
                        TextInput::make('discountPercentage')
                            ->label('Remise (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Couleur'),
                        TextInput::make('icon')
                            ->label('Icône')
                            ->maxLength(50),
                        TextInput::make('perks')
                            ->label('Avantages')
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])->columns(2),
            ]);
    }
}
