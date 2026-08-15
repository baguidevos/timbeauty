<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use App\Models\Barber;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PayrollForm
{
    public static function updateNetSalary(Get $get, Set $set): void
    {
        $fixed = (float) ($get('fixedSalary') ?? 0);
        $commissions = (float) ($get('commissions') ?? 0);
        $bonus = (float) ($get('bonus') ?? 0);
        $advances = (float) ($get('advances') ?? 0);
        $deductions = (float) ($get('deductions') ?? 0);

        $net = ($fixed + $commissions + $bonus) - ($advances + $deductions);
        $set('netSalary', max(0, $net));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('PayrollTabs')
                    ->tabs([
                        Tab::make('Période & Employé')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                Select::make('barberId')
                                    ->label('Employé / Membre du personnel')
                                    ->relationship('barber', 'firstName')
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        $jobLabel = match ($record->jobTitle) {
                                            'barber' => 'Coiffeur',
                                            'manager' => 'Gérant',
                                            'receptionist' => 'Réceptionniste',
                                            'cashier' => 'Caissier',
                                            'cleaner' => 'Entretien',
                                            default => 'Employé',
                                        };

                                        return "{$record->firstName} {$record->lastName} ({$jobLabel})";
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $barber = Barber::find($state);
                                            if ($barber && ! $get('fixedSalary')) {
                                                $set('fixedSalary', $barber->fixedSalary ?? 0);
                                                static::updateNetSalary($get, $set);
                                            }
                                        }
                                    })
                                    ->columnSpanFull(),

                                TextInput::make('month')
                                    ->label('Mois')
                                    ->numeric()
                                    ->default(fn () => (int) now()->format('m'))
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(12),

                                TextInput::make('year')
                                    ->label('Année')
                                    ->numeric()
                                    ->default(fn () => (int) now()->format('Y'))
                                    ->required()
                                    ->minValue(2020),

                                ToggleButtons::make('status')
                                    ->label('Statut de la paie')
                                    ->options([
                                        'draft' => 'Brouillon',
                                        'calculated' => 'Calculé',
                                        'partially_paid' => 'Partiel',
                                        'paid' => 'Payé',
                                    ])
                                    ->colors([
                                        'draft' => 'gray',
                                        'calculated' => 'info',
                                        'partially_paid' => 'warning',
                                        'paid' => 'success',
                                    ])
                                    ->icons([
                                        'draft' => 'heroicon-o-pencil-square',
                                        'calculated' => 'heroicon-o-calculator',
                                        'partially_paid' => 'heroicon-o-clock',
                                        'paid' => 'heroicon-o-check-circle',
                                    ])
                                    ->default('draft')
                                    ->inline()
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Gains & Commissions')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                TextInput::make('fixedSalary')
                                    ->label('Salaire fixe de base (FCFA)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => static::updateNetSalary($get, $set)),

                                TextInput::make('commissions')
                                    ->label('Commissions sur prestations (FCFA)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => static::updateNetSalary($get, $set)),

                                TextInput::make('bonus')
                                    ->label('Primes & Gratifications (FCFA)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => static::updateNetSalary($get, $set)),
                            ])
                            ->columns(3),

                        Tab::make('Déductions & Net à payer')
                            ->icon(Heroicon::OutlinedCalculator)
                            ->schema([
                                TextInput::make('advances')
                                    ->label('Avances & Acomptes versés (FCFA)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => static::updateNetSalary($get, $set)),

                                TextInput::make('deductions')
                                    ->label('Retenues & Déductions diverses (FCFA)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => static::updateNetSalary($get, $set)),

                                TextInput::make('netSalary')
                                    ->label('Salaire net final (FCFA)')
                                    ->numeric()
                                    ->default(0)
                                    ->readOnly()
                                    ->extraInputAttributes(['class' => 'font-bold text-lg text-primary-600 dark:text-primary-400'])
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
