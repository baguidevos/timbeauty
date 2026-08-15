<?php

namespace App\Filament\Resources\StaffAbsences\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffAbsenceForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de l\'absence')
                    ->schema([
                        Select::make('barberId')
                            ->label('Employé')
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
                            ->required(),
                        DatePicker::make('startDate')
                            ->label('Date de début')
                            ->required(),
                        DatePicker::make('endDate')
                            ->label('Date de fin')
                            ->required(),
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'sick' => 'Maladie',
                                'personal' => 'Personnel',
                                'vacation' => 'Congé',
                                'other' => 'Autre',
                            ])
                            ->required()
                            ->native(false),
                        Textarea::make('reason')
                            ->label('Raison')
                            ->maxLength(500),
                    ])->columns(2),
            ]);
    }
}
