<?php

namespace App\Filament\Resources\RevenueTargets\Schemas;

use App\Models\Barber;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RevenueTargetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Configuration de l\'objectif')
                    ->schema([
                        ToggleButtons::make('type')
                            ->label('Type d\'objectif')
                            ->options([
                                'shop' => 'Salon / Boutique',
                                'barber' => 'Personnel / Coiffeur',
                            ])
                            ->colors([
                                'shop' => 'warning',
                                'barber' => 'info',
                            ])
                            ->icons([
                                'shop' => 'heroicon-o-building-storefront',
                                'barber' => 'heroicon-o-user',
                            ])
                            ->default('shop')
                            ->required()
                            ->inline()
                            ->live()
                            ->columnSpanFull(),

                        Select::make('barberId')
                            ->label('Membre du personnel concerné')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(function (Barber $record) {
                                $jobLabel = match ($record->jobTitle) {
                                    'barber' => 'Coiffeur',
                                    'manager' => 'Gérant',
                                    'receptionist' => 'Réceptionniste',
                                    'cashier' => 'Caissier',
                                    default => 'Personnel',
                                };

                                return "{$record->firstName} {$record->lastName} ({$jobLabel})";
                            })
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get) => $get('type') === 'barber')
                            ->visible(fn (Get $get) => $get('type') === 'barber')
                            ->columnSpanFull(),

                        Select::make('month')
                            ->label('Mois')
                            ->options([
                                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                                5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                                9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                            ])
                            ->default((int) now()->format('m'))
                            ->required()
                            ->native(false),

                        Select::make('year')
                            ->label('Année')
                            ->options(array_combine(range(2024, 2030), range(2024, 2030)))
                            ->default((int) now()->format('Y'))
                            ->required()
                            ->native(false),

                        TextInput::make('targetAmount')
                            ->label('Montant de l\'objectif (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(1000)
                            ->placeholder('Ex: 500000')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
