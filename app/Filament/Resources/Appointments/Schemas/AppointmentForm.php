<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations du rendez-vous')
                    ->schema([
                        Select::make('clientId')
                            ->label('Client')
                            ->relationship('client', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('serviceId')
                            ->label('Prestation')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()),
                        TimePicker::make('startTime')
                            ->label('Heure début')
                            ->required(),
                        TimePicker::make('endTime')
                            ->label('Heure fin')
                            ->required(),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'confirmed' => 'Confirmé',
                                'in_progress' => 'En cours',
                                'completed' => 'Terminé',
                                'cancelled' => 'Annulé',
                                'no_show' => 'Non présenté',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
