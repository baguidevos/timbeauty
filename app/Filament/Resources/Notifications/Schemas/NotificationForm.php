<?php

namespace App\Filament\Resources\Notifications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la notification')
                    ->schema([
                        Select::make('clientId')
                            ->label('Client')
                            ->relationship('client', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload(),
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'appointment_reminder' => 'Rappel rendez-vous',
                                'appointment_confirmation' => 'Confirmation rendez-vous',
                                'promotion' => 'Promotion',
                                'loyalty' => 'Fidélité',
                                'general' => 'Général',
                            ])
                            ->required()
                            ->native(false),
                        Select::make('channel')
                            ->label('Canal')
                            ->options([
                                'sms' => 'SMS',
                                'whatsapp' => 'WhatsApp',
                                'email' => 'Email',
                                'push' => 'Push',
                            ])
                            ->required()
                            ->native(false),
                        Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->maxLength(500),
                        Toggle::make('read')
                            ->label('Lu')
                            ->default(false),
                        Toggle::make('sent')
                            ->label('Envoyé')
                            ->default(false),
                        DateTimePicker::make('sentAt')
                            ->label('Envoyé le'),
                    ])->columns(2),
            ]);
    }
}
