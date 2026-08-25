<?php

namespace App\Filament\Resources\OwnerAdvances\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OwnerAdvanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('userId')
                    ->label('Propriétaire / Apporteur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->default(auth()->id())
                    ->required(),

                Select::make('cashRegisterId')
                    ->label('Session de Caisse associée')
                    ->relationship('cashRegister', 'id')
                    ->placeholder('Aucune session (Apport hors caisse)')
                    ->searchable()
                    ->preload(),

                TextInput::make('amount')
                    ->label('Montant apporté (FCFA)')
                    ->numeric()
                    ->required()
                    ->minValue(1),

                TextInput::make('reason')
                    ->label('Motif de l\'avance')
                    ->placeholder('Ex: Paie des salariés, Achat stock urgent...')
                    ->default('Avance de trésorerie')
                    ->required()
                    ->maxLength(255),

                Select::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente de remboursement',
                        'partially_refunded' => 'Partiellement remboursé',
                        'refunded' => 'Totalement remboursé / Soldé',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false),

                Textarea::make('notes')
                    ->label('Notes complémentaires')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
