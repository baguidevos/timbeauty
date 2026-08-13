<?php

namespace App\Filament\Resources\LoyaltyPointTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoyaltyPointTransactionForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la transaction')
                    ->schema([
                        Select::make('clientId')
                            ->label('Client')
                            ->relationship('client', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('points')
                            ->label('Points')
                            ->numeric()
                            ->required(),
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'earn' => 'Gagné',
                                'redeem' => 'Utilisé',
                                'expire' => 'Expiré',
                                'adjust' => 'Ajustement',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('description')
                            ->label('Description')
                            ->maxLength(255),
                        Select::make('saleId')
                            ->label('Vente associée')
                            ->relationship('sale', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 'Vente #'.$record->id)
                            ->searchable(),
                        TextInput::make('reference')
                            ->label('Référence')
                            ->maxLength(50),
                    ])->columns(2),
            ]);
    }
}
