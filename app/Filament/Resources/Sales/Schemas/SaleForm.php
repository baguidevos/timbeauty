<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la vente')
                    ->schema([
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
                            ->preload()
                            ->required(),
                        Select::make('paymentMethod')
                            ->label('Méthode de paiement')
                            ->options([
                                'cash' => 'Espèces',
                                'tmoney' => 'TMoney',
                                'flooz' => 'Flooz',
                                'card' => 'Carte',
                                'transfer' => 'Virement',
                                'other' => 'Autre',
                            ])
                            ->default('cash')
                            ->required()
                            ->native(false),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'completed' => 'Terminée',
                                'cancelled' => 'Annulée',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
                Section::make('Montants')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Sous-total (FCFA)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('discountAmount')
                            ->label('Remise (FCFA)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('total')
                            ->label('Total (FCFA)')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }
}
