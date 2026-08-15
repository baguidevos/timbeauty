<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('ProductTabs')
                    ->tabs([
                        Tab::make('Informations du produit')
                            ->icon(Heroicon::OutlinedCube)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('reference')
                                    ->label('Référence')
                                    ->placeholder('Ex: PRD-AB12CD')
                                    ->suffixAction(
                                        Action::make('generateReference')
                                            ->icon('heroicon-o-arrow-path')
                                            ->tooltip('Générer une référence')
                                            ->action(function (Set $set): void {
                                                $set('reference', 'PRD-'.strtoupper(Str::random(6)));
                                            })
                                    )
                                    ->maxLength(50),

                                Select::make('categoryId')
                                    ->label('Catégorie')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nom de la catégorie')
                                            ->required()
                                            ->maxLength(100),
                                        Textarea::make('description')
                                            ->label('Description')
                                            ->maxLength(500),
                                    ]),

                                TextInput::make('brand')
                                    ->label('Marque')
                                    ->maxLength(100),
                                Select::make('supplierId')
                                    ->label('Fournisseur')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload(),
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
                                    ->required(),
                                Textarea::make('description')
                                    ->label('Description')
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Prix & Stock')
                            ->icon(Heroicon::OutlinedCircleStack)
                            ->schema([
                                TextInput::make('purchasePrice')
                                    ->label('Prix d\'achat (FCFA)')
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('sellingPrice')
                                    ->label('Prix de vente (FCFA)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0),
                                TextInput::make('stockQuantity')
                                    ->label('Quantité en stock')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                TextInput::make('minStockLevel')
                                    ->label('Stock minimum d\'alerte')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
