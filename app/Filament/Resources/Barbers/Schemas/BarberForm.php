<?php

namespace App\Filament\Resources\Barbers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BarberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('BarberTabs')
                    ->tabs([
                        Tab::make('Informations personnelles')
                            ->icon(Heroicon::OutlinedUser)
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
                                    ->label('Date d\'embauche')
                                    ->default(now())
                                    ->native(false),
                                ToggleButtons::make('status')
                                    ->label('Statut')
                                    ->options([
                                        'active' => 'Actif',
                                        'inactive' => 'Inactif',
                                        'on_leave' => 'En congé',
                                    ])
                                    ->colors([
                                        'active' => 'success',
                                        'inactive' => 'danger',
                                        'on_leave' => 'warning',
                                    ])
                                    ->icons([
                                        'active' => 'heroicon-o-check-circle',
                                        'inactive' => 'heroicon-o-x-circle',
                                        'on_leave' => 'heroicon-o-clock',
                                    ])
                                    ->default('active')
                                    ->inline()
                                    ->required(),
                                TextInput::make('specialties')
                                    ->label('Spécialités')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Rémunération')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                ToggleButtons::make('remunerationType')
                                    ->label('Type de rémunération')
                                    ->options([
                                        'fixed' => 'Salaire fixe',
                                        'commission' => 'Commission',
                                        'fixed_plus_commission' => 'Fixe + Commission',
                                        'per_service' => 'Par prestation',
                                    ])
                                    ->colors([
                                        'fixed' => 'info',
                                        'commission' => 'warning',
                                        'fixed_plus_commission' => 'primary',
                                        'per_service' => 'success',
                                    ])
                                    ->icons([
                                        'fixed' => 'heroicon-o-currency-dollar',
                                        'commission' => 'heroicon-o-chart-bar',
                                        'fixed_plus_commission' => 'heroicon-o-banknotes',
                                        'per_service' => 'heroicon-o-scissors',
                                    ])
                                    ->required()
                                    ->inline()
                                    ->live()
                                    ->columnSpanFull(),
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
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
