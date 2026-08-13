<?php

namespace App\Filament\Resources\AppointmentPhotos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentPhotoForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la photo')
                    ->schema([
                        Select::make('appointmentId')
                            ->label('Rendez-vous')
                            ->relationship('appointment', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 'RDV #'.$record->id.' - '.$record->date?->format('d/m/Y'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('clientId')
                            ->label('Client')
                            ->relationship('client', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload(),
                        Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload(),
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'before' => 'Avant',
                                'after' => 'Après',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('url')
                            ->label('URL de la photo')
                            ->url()
                            ->required()
                            ->maxLength(500),
                        TextInput::make('caption')
                            ->label('Légende')
                            ->maxLength(255),
                        TextInput::make('tags')
                            ->label('Étiquettes')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }
}
