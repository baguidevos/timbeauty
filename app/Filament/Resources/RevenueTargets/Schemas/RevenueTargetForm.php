<?php

namespace App\Filament\Resources\RevenueTargets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RevenueTargetForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de l\'objectif')
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'shop' => 'Salon',
                                'barber' => 'Coiffeur',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                        Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('type') === 'barber'),
                        TextInput::make('month')
                            ->label('Mois')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(12),
                        TextInput::make('year')
                            ->label('Année')
                            ->numeric()
                            ->required()
                            ->minValue(2020),
                        TextInput::make('targetAmount')
                            ->label('Montant objectif (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                    ])->columns(2),
            ]);
    }
}
