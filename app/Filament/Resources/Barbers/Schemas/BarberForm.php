<?php

namespace App\Filament\Resources\Barbers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                        Tab::make('Informations générales')
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
                                ToggleButtons::make('jobTitle')
                                    ->label('Poste / Fonction')
                                    ->options([
                                        'barber' => 'Coiffeur / Barbier',
                                        'manager' => 'Gérant / Manager',
                                        'receptionist' => 'Réceptionniste',
                                        'cashier' => 'Caissier',
                                        'cleaner' => 'Entretien',
                                        'other' => 'Autre',
                                    ])
                                    ->colors([
                                        'barber' => 'primary',
                                        'manager' => 'warning',
                                        'receptionist' => 'info',
                                        'cashier' => 'success',
                                        'cleaner' => 'gray',
                                        'other' => 'gray',
                                    ])
                                    ->icons([
                                        'barber' => 'heroicon-o-scissors',
                                        'manager' => 'heroicon-o-briefcase',
                                        'receptionist' => 'heroicon-o-phone',
                                        'cashier' => 'heroicon-o-calculator',
                                        'cleaner' => 'heroicon-o-sparkles',
                                        'other' => 'heroicon-o-user',
                                    ])
                                    ->default('barber')
                                    ->inline()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state === 'barber') {
                                            $set('canPerformServices', true);
                                        } else {
                                            $set('canPerformServices', false);
                                            $set('remunerationType', 'fixed');
                                        }
                                    })
                                    ->required()
                                    ->columnSpanFull(),
                                Toggle::make('canPerformServices')
                                    ->label('Effectue des prestations (visible dans le planning / RDV)')
                                    ->default(true)
                                    ->live()
                                    ->columnSpanFull(),
                                TextInput::make('specialties')
                                    ->label('Spécialités & Compétences')
                                    ->placeholder('Ex: Dégradé américain, Barbe, Coloration, Soins...')
                                    ->maxLength(255)
                                    ->visible(fn (Get $get) => $get('canPerformServices') || $get('jobTitle') === 'barber')
                                    ->columnSpanFull(),
                                Select::make('userId')
                                    ->label('Compte utilisateur lié (accès au logiciel)')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Optionnel : associe cet employé à un compte utilisateur pour se connecter.')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Rémunération')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                ToggleButtons::make('remunerationType')
                                    ->label('Type de rémunération')
                                    ->options(fn (Get $get) => ($get('canPerformServices') || $get('jobTitle') === 'barber') ? [
                                        'fixed' => 'Salaire fixe',
                                        'commission' => 'Commission',
                                        'fixed_plus_commission' => 'Fixe + Commission',
                                        'per_service' => 'Par prestation',
                                    ] : [
                                        'fixed' => 'Salaire fixe',
                                        'commission' => 'Commission',
                                        'fixed_plus_commission' => 'Fixe + Commission',
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
                                    ->default('fixed')
                                    ->required()
                                    ->inline()
                                    ->live()
                                    ->columnSpanFull(),
                                TextInput::make('fixedSalary')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Le salaire fixe est requis, 0 par defaut',
                                        'numeric' => 'Le salaire fixe doit être un nombre',
                                        'step' => 'Le salaire fixe doit être un nombre avec 2 décimales',
                                        'min' => 'Le salaire fixe doit être supérieur ou égal à 0',
                                    ])
                                    ->default(0)
                                    ->label('Salaire fixe (FCFA)')
                                    ->numeric()
                                    ->visible(fn (Get $get) => in_array($get('remunerationType'), ['fixed', 'fixed_plus_commission'])),
                                TextInput::make('commissionRate')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Le taux de commission est requis, 0 par defaut',
                                        'numeric' => 'Le taux de commission doit être un nombre',
                                        'step' => 'Le taux de commission doit être un nombre avec 2 décimales',
                                        'min' => 'Le taux de commission doit être supérieur ou égal à 0',
                                        'max' => 'Le taux de commission doit être inférieur ou égal à 100',
                                    ])
                                    ->default(0)
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
