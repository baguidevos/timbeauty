<?php

namespace App\Filament\Resources\Services;

use App\Filament\Clusters\CatalogCluster;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\Tables\ServicesTable;
use App\Models\Service;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $cluster = CatalogCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scissors';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Prestations';

    protected static ?string $modelLabel = 'Prestation';

    protected static ?string $pluralModelLabel = 'Prestations';

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            // 'create' => Pages\CreateService::route('/create'),
            'view' => Pages\ViewService::route('/{record}'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
