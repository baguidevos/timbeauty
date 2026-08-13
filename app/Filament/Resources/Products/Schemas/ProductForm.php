<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations du produit')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('reference')
                            ->label('Référence')
                            ->maxLength(50),
                        Select::make('categoryId')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('brand')
                            ->label('Marque')
                            ->maxLength(100),
                        TextInput::make('description')
                            ->label('Description')
                            ->maxLength(500),
                    ])->columns(2),
                Section::make('Prix et stock')
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
                            ->label('Stock minimum')
                            ->numeric()
                            ->default(0),
                        Select::make('supplierId')
                            ->label('Fournisseur')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])->columns(3),
            ]);
    }
}
