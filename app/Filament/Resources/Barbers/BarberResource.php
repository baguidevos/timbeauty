<?php

namespace App\Filament\Resources\Barbers;

use App\Filament\Resources\Barbers\Schemas\BarberForm;
use App\Filament\Resources\Barbers\Tables\BarbersTable;
use App\Models\Barber;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BarberResource extends Resource
{
    protected static ?string $model = Barber::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Personnel';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 20;

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
        return [];
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
