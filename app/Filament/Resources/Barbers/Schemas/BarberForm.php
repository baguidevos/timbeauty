<?php

namespace App\Filament\Resources\Barbers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class BarberForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations personnelles')
                    ->schema([
                        TextInput::make('firstName')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('lastName')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('address')
                            ->label('Adresse')
                            ->maxLength(255),
                        DatePicker::make('hireDate')
                            ->label('Date d\'embauche'),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                                'on_leave' => 'En congé',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                        TextInput::make('specialties')
                            ->label('Spécialités')
                            ->maxLength(255),
                    ])->columns(2),
                Section::make('Rémunération')
                    ->schema([
                        Select::make('remunerationType')
                            ->label('Type de rémunération')
                            ->options([
                                'fixed' => 'Salaire fixe',
                                'commission' => 'Commission',
                                'fixed_plus_commission' => 'Fixe + Commission',
                                'per_service' => 'Par prestation',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                        TextInput::make('fixedSalary')
                            ->label('Salaire fixe (FCFA)')
                            ->numeric()
                            ->visible(fn (Get $get) => in_array($get('remunerationType'), ['fixed', 'fixed_plus_commission'])),
                        TextInput::make('commissionRate')
                            ->label('Taux de commission (%)')
                            ->numeric()
                            ->step(0.01)
                            ->visible(fn (Get $get) => in_array($get('remunerationType'), ['commission', 'fixed_plus_commission'])),
                        TextInput::make('perServiceRate')
                            ->label('Tarif par prestation (FCFA)')
                            ->numeric()
                            ->visible(fn (Get $get) => $get('remunerationType') === 'per_service'),
                    ])->columns(2),
                Section::make('Compte utilisateur')
                    ->schema([
                        Select::make('userId')
                            ->label('Utilisateur associé')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}
