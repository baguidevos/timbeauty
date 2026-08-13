<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceCategoryResource\Pages;
use App\Models\ServiceCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceCategoryResource extends Resource
{
    protected static ?string $model = ServiceCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 35;

    protected static ?string $modelLabel = 'Catégorie de prestation';

    protected static ?string $pluralModelLabel = 'Catégories de prestations';

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
                        Forms\Components\TextInput::make('icon')
                            ->label('Icône')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('order')
                            ->label('Ordre')
                            ->numeric()
                            ->default(0),
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
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icône')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('services_count')
                    ->label('Prestations')
                    ->counts('services')
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
            ->defaultSort('order', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceCategories::route('/'),
            'create' => Pages\CreateServiceCategory::route('/create'),
            'edit' => Pages\EditServiceCategory::route('/{record}/edit'),
        ];
    }
}
