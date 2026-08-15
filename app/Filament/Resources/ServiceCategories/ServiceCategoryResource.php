<?php

namespace App\Filament\Resources\ServiceCategories;

use App\Filament\Resources\ServiceCategories\Schemas\ServiceCategoryForm;
use App\Filament\Resources\ServiceCategories\Tables\ServiceCategoriesTable;
use App\Models\ServiceCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

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
        return ServiceCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceCategoriesTable::configure($table);
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

    #[Override]
    public static function canAccess(): bool
    {
        return false;
    }
}
