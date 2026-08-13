<?php

namespace App\Filament\Resources\CashRegisters\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CashRegisterForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la caisse')
                    ->schema([
                        TextInput::make('openingAmount')
                            ->label('Montant d\'ouverture (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('closingAmount')
                            ->label('Montant de clôture (FCFA)')
                            ->numeric()
                            ->visible(fn (Get $get) => $get('status') === 'closed'),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'open' => 'Ouverte',
                                'closed' => 'Fermée',
                            ])
                            ->default('open')
                            ->required()
                            ->native(false)
                            ->live(),
                        DateTimePicker::make('openedAt')
                            ->label('Ouverte le')
                            ->default(now()),
                        DateTimePicker::make('closedAt')
                            ->label('Fermée le')
                            ->visible(fn (Get $get) => $get('status') === 'closed'),
                    ])->columns(2),
            ]);
    }
}
