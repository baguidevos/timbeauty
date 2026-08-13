<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseCategoryResource\Pages;
use App\Models\ExpenseCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 15;

    protected static ?string $modelLabel = 'Catégorie de dépense';

    protected static ?string $pluralModelLabel = 'Catégories de dépenses';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('icon')
                    ->label('Icône')
                    ->maxLength(50),
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
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icône')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('expenses_count')
                    ->label('Dépenses')
                    ->counts('expenses')
                    ->numeric(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenseCategories::route('/'),
            'create' => Pages\CreateExpenseCategory::route('/create'),
            'edit' => Pages\EditExpenseCategory::route('/{record}/edit'),
        ];
    }
}
