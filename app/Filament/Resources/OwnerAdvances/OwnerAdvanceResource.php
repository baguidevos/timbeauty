<?php

namespace App\Filament\Resources\OwnerAdvances;

use App\Filament\Resources\OwnerAdvances\Pages\ListOwnerAdvances;
use App\Filament\Resources\OwnerAdvances\RelationManagers\RefundsRelationManager;
use App\Filament\Resources\OwnerAdvances\Schemas\OwnerAdvanceForm;
use App\Filament\Resources\OwnerAdvances\Tables\OwnerAdvancesTable;
use App\Models\OwnerAdvance;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class OwnerAdvanceResource extends Resource
{
    protected static ?string $model = OwnerAdvance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 42;

    protected static ?string $modelLabel = 'Avance Propriétaire';

    protected static ?string $pluralModelLabel = 'Avances Propriétaires';

    public static function form(Schema $schema): Schema
    {
        return OwnerAdvanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OwnerAdvancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RefundsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOwnerAdvances::route('/'),
        ];
    }
}
