<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la dépense')
                    ->schema([
                        Select::make('categoryId')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('amount')
                            ->label('Montant (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()),
                        TextInput::make('beneficiary')
                            ->label('Bénéficiaire')
                            ->maxLength(100),
                        Select::make('paymentMethod')
                            ->label('Méthode de paiement')
                            ->options([
                                'cash' => 'Espèces',
                                'card' => 'Carte',
                                'transfer' => 'Virement',
                                'check' => 'Chèque',
                                'other' => 'Autre',
                            ])
                            ->default('cash')
                            ->native(false),
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
