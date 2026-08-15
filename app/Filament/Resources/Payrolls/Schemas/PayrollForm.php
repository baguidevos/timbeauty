<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use App\Models\Barber;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

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
                Section::make('Informations de la paie')
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
                            }),
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
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'calculated' => 'Calculé',
                                'paid' => 'Payé',
                            ])
                            ->default('draft')
                            ->required()
                            ->native(false),
                    ])->columns(2),
                Section::make('Détail des montants (FCFA)')
                    ->schema([
                        TextInput::make('fixedSalary')
                            ->label('Salaire fixe')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => static::updateNetSalary($get, $set)),
                        TextInput::make('commissions')
                            ->label('Commissions')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => static::updateNetSalary($get, $set)),
                        TextInput::make('bonus')
                            ->label('Prime')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => static::updateNetSalary($get, $set)),
                        TextInput::make('advances')
                            ->label('Avances')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => static::updateNetSalary($get, $set)),
                        TextInput::make('deductions')
                            ->label('Déductions')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => static::updateNetSalary($get, $set)),
                        TextInput::make('netSalary')
                            ->label('Salaire net à payer')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->extraInputAttributes(['class' => 'font-bold text-primary-600 dark:text-primary-400']),
                    ])->columns(3),
            ]);
    }
}
