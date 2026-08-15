<?php

namespace App\Filament\Resources\LoyaltyRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class LoyaltyRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('LoyaltyRuleTabs')
                    ->tabs([
                        Tab::make('Palier & Récompense')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom du palier / récompense')
                                    ->placeholder('Ex: Récompense 5 visites (-20%), VIP 10 visites...')
                                    ->required()
                                    ->maxLength(100)
                                    ->columnSpanFull(),

                                TextInput::make('requiredVisits')
                                    ->label('Nombre de visites requises')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(5)
                                    ->prefix('🎯')
                                    ->helperText('Nombre de prestations réalisées avant de déclencher la récompense.'),

                                TextInput::make('discountPercentage')
                                    ->label('Pourcentage de remise')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(20)
                                    ->suffix('%')
                                    ->helperText('Ex: 20% ou 100% pour une prestation 100% offerte.'),

                                Select::make('serviceId')
                                    ->label('Prestation concernée')
                                    ->relationship('service', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Toutes les prestations du salon')
                                    ->helperText('Laissez vide pour appliquer la remise à n\'importe quelle prestation.')
                                    ->columnSpanFull(),

                                Textarea::make('message')
                                    ->label('Message de félicitations / SMS')
                                    ->placeholder('Ex: Félicitations ! Vous avez atteint 5 visites et bénéficiez de -20% sur votre prochaine coupe.')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Validité & Délais')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                TextInput::make('validityDays')
                                    ->label('Durée de validité du bon (en jours)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(15)
                                    ->suffix('jours')
                                    ->helperText('Délai dont dispose le client pour utiliser sa récompense.'),

                                TextInput::make('cooldownDays')
                                    ->label('Période de temporisation (cooldown)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(30)
                                    ->suffix('jours')
                                    ->helperText('Nombre de jours avant de pouvoir réaccumuler pour ce palier.'),

                                ToggleButtons::make('status')
                                    ->label('Statut de la règle')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
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
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
