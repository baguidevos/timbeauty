<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la paie')
                    ->schema([
                        Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
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
                Section::make('Montants')
                    ->schema([
                        TextInput::make('fixedSalary')
                            ->label('Salaire fixe (FCFA)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('commissions')
                            ->label('Commissions (FCFA)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('bonus')
                            ->label('Prime (FCFA)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('advances')
                            ->label('Avances (FCFA)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('deductions')
                            ->label('Déductions (FCFA)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('netSalary')
                            ->label('Salaire net (FCFA)')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }
}
