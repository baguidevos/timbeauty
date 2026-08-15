<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
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
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nom de la catégorie')
                                    ->required()
                                    ->maxLength(100),
                            ]),
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
                        ToggleButtons::make('paymentMethod')
                            ->label('Méthode de paiement')
                            ->options([
                                'cash' => 'Espèces',
                                'card' => 'Carte',
                                'transfer' => 'Virement',
                                'check' => 'Chèque',
                                'other' => 'Autre',
                            ])
                            ->colors([
                                'cash' => 'success',
                                'card' => 'info',
                                'transfer' => 'primary',
                                'check' => 'warning',
                                'other' => 'gray',
                            ])
                            ->icons([
                                'cash' => 'heroicon-o-banknotes',
                                'card' => 'heroicon-o-credit-card',
                                'transfer' => 'heroicon-o-arrow-path-rounded-square',
                                'check' => 'heroicon-o-document-check',
                                'other' => 'heroicon-o-ellipsis-horizontal',
                            ])
                            ->default('cash')
                            ->inline()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Hidden::make('createdBy')
                            ->default(fn () => auth()->id()),
                    ])->columns(2),
            ]);
    }
}
