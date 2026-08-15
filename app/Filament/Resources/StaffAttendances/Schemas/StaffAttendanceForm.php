<?php

namespace App\Filament\Resources\StaffAttendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de présence')
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
                        DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()),
                        DateTimePicker::make('clockIn')
                            ->label('Heure d\'arrivée'),
                        DateTimePicker::make('clockOut')
                            ->label('Heure de départ'),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'present' => 'Présent',
                                'late' => 'En retard',
                                'absent' => 'Absent',
                                'half_day' => 'Demi-journée',
                            ])
                            ->default('present')
                            ->required()
                            ->native(false),
                        TextInput::make('workedMinutes')
                            ->label('Minutes travaillées')
                            ->numeric()
                            ->default(0),
                        TextInput::make('breakMinutes')
                            ->label('Minutes de pause')
                            ->numeric()
                            ->default(0),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500),
                    ])->columns(2),
            ]);
    }
}
