<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductCategoryResource\Pages;
use App\Models\ProductCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 25;

    protected static ?string $modelLabel = 'Catégorie de produit';

    protected static ?string $pluralModelLabel = 'Catégories de produits';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la catégorie')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500),
                    ])->columns(2),
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
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produits')
                    ->counts('products')
                    ->numeric(),
            ])
            ->recordActions([
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
            'index' => Pages\ListProductCategories::route('/'),
            'create' => Pages\CreateProductCategory::route('/create'),
            'edit' => Pages\EditProductCategory::route('/{record}/edit'),
        ];
    }
}
