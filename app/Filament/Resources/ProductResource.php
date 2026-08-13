<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Helpers\FormatHelper;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Produit';

    protected static ?string $pluralModelLabel = 'Produits';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations du produit')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence')
                            ->maxLength(50),
                        Forms\Components\Select::make('categoryId')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('brand')
                            ->label('Marque')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->maxLength(500),
                    ])->columns(2),
                Forms\Components\Section::make('Prix et stock')
                    ->schema([
                        Forms\Components\TextInput::make('purchasePrice')
                            ->label('Prix d\'achat (FCFA)')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('sellingPrice')
                            ->label('Prix de vente (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('stockQuantity')
                            ->label('Quantité en stock')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('minStockLevel')
                            ->label('Stock minimum')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('supplierId')
                            ->label('Fournisseur')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sellingPrice')
                    ->label('Prix de vente')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('stockQuantity')
                    ->label('Stock')
                    ->numeric()
                    ->color(fn ($record) => $record->stockQuantity <= $record->minStockLevel ? 'danger' : 'success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Fournisseur')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                    ]),
                Tables\Filters\SelectFilter::make('categoryId')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('supplierId')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
