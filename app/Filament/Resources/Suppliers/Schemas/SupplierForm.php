<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations du fournisseur')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('contactName')
                            ->label('Nom du contact')
                            ->maxLength(100),
                        TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(100),
                        TextInput::make('address')
                            ->label('Adresse')
                            ->maxLength(255),
                        TextInput::make('paymentTerms')
                            ->label('Conditions de paiement')
                            ->maxLength(100),
                        ToggleButtons::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                            ])
                            ->colors([
                                'active' => 'success',
                                'inactive' => 'danger',
                            ])
                            ->icons([
                                'active' => 'heroicon-o-check-circle',
                                'inactive' => 'heroicon-o-x-circle',
                            ])
                            ->default('active')
                            ->inline()
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
