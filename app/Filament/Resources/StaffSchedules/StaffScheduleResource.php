<?php

namespace App\Filament\Resources\StaffSchedules;

use App\Filament\Resources\StaffSchedules\Schemas\StaffScheduleForm;
use App\Filament\Resources\StaffSchedules\Tables\StaffSchedulesTable;
use App\Models\StaffSchedule;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StaffScheduleResource extends Resource
{
    protected static ?string $model = StaffSchedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 32;

    protected static ?string $modelLabel = 'Emploi du temps';

    protected static ?string $pluralModelLabel = 'Emplois du temps';

    public static function form(Schema $schema): Schema
    {
        return StaffScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffSchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffSchedules::route('/'),
            'create' => Pages\CreateStaffSchedule::route('/create'),
            'edit' => Pages\EditStaffSchedule::route('/{record}/edit'),
        ];
    }
}
