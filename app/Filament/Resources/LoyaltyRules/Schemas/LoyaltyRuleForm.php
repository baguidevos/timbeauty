<?php

namespace App\Filament\Resources\LoyaltyRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoyaltyRuleForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la règle')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('requiredVisits')
                            ->label('Visites requises')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Select::make('serviceId')
                            ->label('Prestation')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('discountPercentage')
                            ->label('Remise (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        TextInput::make('message')
                            ->label('Message')
                            ->maxLength(255),
                        TextInput::make('cooldownDays')
                            ->label('Jours de refroidissement')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('validityDays')
                            ->label('Validité (jours)')
                            ->numeric()
                            ->minValue(1),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])->columns(2),
            ]);
    }
}
