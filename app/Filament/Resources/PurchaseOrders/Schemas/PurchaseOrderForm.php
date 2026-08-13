<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la commande')
                    ->schema([
                        TextInput::make('reference')
                            ->label('Référence')
                            ->required()
                            ->maxLength(50),
                        Select::make('supplierId')
                            ->label('Fournisseur')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('orderDate')
                            ->label('Date de commande')
                            ->required()
                            ->default(now()),
                        DatePicker::make('expectedDate')
                            ->label('Date prévue'),
                        DatePicker::make('receivedDate')
                            ->label('Date de réception'),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'ordered' => 'Commandée',
                                'received' => 'Reçue',
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
                        TextInput::make('totalAmount')
                            ->label('Montant total (FCFA)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('paidAmount')
                            ->label('Montant payé (FCFA)')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }
}
