<?php

namespace App\Filament\Resources\Barbers;

use App\Filament\Clusters\HumanResources;
use App\Filament\Resources\Barbers\RelationManagers\AbsencesRelationManager;
use App\Filament\Resources\Barbers\RelationManagers\SchedulesRelationManager;
use App\Filament\Resources\Barbers\Schemas\BarberForm;
use App\Filament\Resources\Barbers\Tables\BarbersTable;
use App\Models\Barber;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BarberResource extends Resource
{
    protected static ?string $model = Barber::class;

    protected static ?string $cluster = HumanResources::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Personnel & Barbiers';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Membre du personnel';

    protected static ?string $pluralModelLabel = 'Personnel / Employés';

    public static function form(Schema $schema): Schema
    {
        return BarberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BarbersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SchedulesRelationManager::class,
            AbsencesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarbers::route('/'),
            // 'create' => Pages\CreateBarber::route('/create'),
            'view' => Pages\ViewBarber::route('/{record}'),
            'edit' => Pages\EditBarber::route('/{record}/edit'),
        ];
    }
}
