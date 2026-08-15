<?php

namespace App\Filament\Resources\ExpenseCategories;

use App\Filament\Resources\ExpenseCategories\Schemas\ExpenseCategoryForm;
use App\Filament\Resources\ExpenseCategories\Tables\ExpenseCategoriesTable;
use App\Models\ExpenseCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 15;

    protected static ?string $modelLabel = 'Catégorie de dépense';

    protected static ?string $pluralModelLabel = 'Catégories de dépenses';

    #[Override]
    public static function canAccess(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ExpenseCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpenseCategoriesTable::configure($table);
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
