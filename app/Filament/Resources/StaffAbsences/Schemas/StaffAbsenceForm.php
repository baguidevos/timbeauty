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
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
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
