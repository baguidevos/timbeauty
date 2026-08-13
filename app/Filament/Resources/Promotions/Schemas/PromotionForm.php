<?php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la promotion')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500),
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'percentage' => 'Pourcentage',
                                'fixed' => 'Montant fixe',
                                'free_service' => 'Prestation gratuite',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('value')
                            ->label('Valeur')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        DatePicker::make('startDate')
                            ->label('Date de début'),
                        DatePicker::make('endDate')
                            ->label('Date de fin'),
                    ])->columns(2),
                Section::make('Conditions')
                    ->schema([
                        TextInput::make('minVisits')
                            ->label('Visites minimum')
                            ->numeric()
                            ->default(0),
                        Toggle::make('forLoyalOnly')
                            ->label('Clients fidèles uniquement')
                            ->default(false),
                        TextInput::make('maxUsages')
                            ->label('Utilisations maximum')
                            ->numeric(),
                        TextInput::make('currentUsages')
                            ->label('Utilisations actuelles')
                            ->numeric()
                            ->default(0),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'expired' => 'Expirée',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])->columns(2),
            ]);
    }
}
