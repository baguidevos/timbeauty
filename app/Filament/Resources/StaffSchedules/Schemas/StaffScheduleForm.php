<?php

namespace App\Filament\Resources\StaffSchedules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffScheduleForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Emploi du temps')
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
                        Select::make('dayOfWeek')
                            ->label('Jour de la semaine')
                            ->options([
                                0 => 'Dimanche',
                                1 => 'Lundi',
                                2 => 'Mardi',
                                3 => 'Mercredi',
                                4 => 'Jeudi',
                                5 => 'Vendredi',
                                6 => 'Samedi',
                            ])
                            ->required()
                            ->native(false),
                        TimePicker::make('startTime')
                            ->label('Heure de début'),
                        TimePicker::make('endTime')
                            ->label('Heure de fin'),
                        Toggle::make('isDayOff')
                            ->label('Jour de repos')
                            ->default(false),
                    ])->columns(2),
            ]);
    }
}
