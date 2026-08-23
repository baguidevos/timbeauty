<?php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('PromotionTabs')
                    ->tabs([
                        Tab::make('Offre & Réduction')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom de la promotion')
                                    ->placeholder('Ex: Offre Rentrée, Promo Happy Hour, Pack VIP...')
                                    ->required()
                                    ->maxLength(100)
                                    ->columnSpanFull(),

                                ToggleButtons::make('type')
                                    ->label('Type de réduction')
                                    ->options([
                                        'percentage' => 'Pourcentage (%)',
                                        'fixed' => 'Montant fixe (FCFA)',
                                        'free_service' => 'Prestation offerte',
                                    ])
                                    ->colors([
                                        'percentage' => 'warning',
                                        'fixed' => 'success',
                                        'free_service' => 'primary',
                                    ])
                                    ->icons([
                                        'percentage' => 'heroicon-o-receipt-percent',
                                        'fixed' => 'heroicon-o-banknotes',
                                        'free_service' => 'heroicon-o-gift',
                                    ])
                                    ->default('percentage')
                                    ->required()
                                    ->live()
                                    ->inline()
                                    ->columnSpanFull(),

                                TextInput::make('value')
                                    ->label('Valeur de la réduction')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->suffix(fn (Get $get) => $get('type') === 'percentage' ? '%' : ($get('type') === 'fixed' ? 'FCFA' : ''))
                                    ->hidden(fn (Get $get) => $get('type') === 'free_service')
                                    ->default(10),

                                Select::make('categories')
                                    ->label('Catégories de prestations éligibles')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->placeholder('Toutes les catégories de prestations si vide')
                                    ->helperText('Laissez vide pour appliquer la promotion à l\'ensemble des catégories de prestations.')
                                    ->columnSpanFull(),

                                DatePicker::make('startDate')
                                    ->label('Date de début')
                                    ->default(now())
                                    ->native(false),

                                DatePicker::make('endDate')
                                    ->label('Date de fin')
                                    ->native(false),

                                Textarea::make('description')
                                    ->label('Description & Détails de l\'offre')
                                    ->placeholder('Détails de l\'opération marketing...')
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Conditions & Quotas')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                ToggleButtons::make('status')
                                    ->label('Statut de l\'offre')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Désactivée',
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

                                Toggle::make('forLoyalOnly')
                                    ->label('Réservée exclusivement aux clients fidèles')
                                    ->helperText('Seuls les clients avec le badge "Fidèle" pourront bénéficier de cette offre.')
                                    ->default(false)
                                    ->columnSpanFull(),

                                TextInput::make('minVisits')
                                    ->label('Visites antérieures minimum requises')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->helperText('0 = accessible sans condition d\'ancienneté'),

                                TextInput::make('maxUsages')
                                    ->label('Plafond d\'utilisations global')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Illimité si vide')
                                    ->helperText('Nombre total de fois où cette promo peut être utilisée.'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
