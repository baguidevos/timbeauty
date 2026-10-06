<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('ClientTabs')
                    ->tabs([
                        Tab::make('Informations personnelles')
                            ->icon(Heroicon::OutlinedUser)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Code client')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Généré automatiquement (ex: ES2233-1)')
                                    ->columnSpanFull(),
                                TextInput::make('firstName')
                                    ->label('Prénom')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('lastName')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('phone')
                                    ->label('Téléphone')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('whatsapp')
                                    ->label('WhatsApp')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(100),
                                Select::make('gender')
                                    ->label('Genre')
                                    ->options([
                                        'male' => 'Homme',
                                        'female' => 'Femme',
                                        'other' => 'Autre',
                                    ])
                                    ->native(false),
                                DatePicker::make('birthDate')
                                    ->native(false)
                                    ->label('Date de naissance'),
                                TextInput::make('address')
                                    ->label('Adresse')
                                    ->maxLength(255),
                            ])
                            ->columns(2),

                        Tab::make('Fidélité')
                            ->icon(Heroicon::OutlinedStar)
                            ->schema([
                                DatePicker::make('firstVisitDate')
                                    ->native(false)
                                    ->label('Date de la première visite')
                                    ->default(now()),
                                Toggle::make('isLoyal')
                                    ->label('Client fidèle'),
                                TextInput::make('loyaltyPoints')
                                    ->label('Points de fidélité')
                                    ->numeric()
                                    ->default(0),
                                Select::make('loyaltyTierId')
                                    ->label('Niveau de fidélité')
                                    ->relationship('loyaltyTier', 'name')
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(3),

                        Tab::make('Notes')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
