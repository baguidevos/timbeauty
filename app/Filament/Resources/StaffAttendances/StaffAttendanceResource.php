<?php

namespace App\Filament\Resources\StaffAttendances;

use App\Filament\Resources\StaffAttendances\Schemas\StaffAttendanceForm;
use App\Filament\Resources\StaffAttendances\Tables\StaffAttendancesTable;
use App\Filament\Resources\StaffAttendances\Widgets\StaffAttendanceWidget;
use App\Models\StaffAttendance;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StaffAttendanceResource extends Resource
{
    protected static ?string $model = StaffAttendance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationLabel = 'Pointage & Présences';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Pointage';

    protected static ?string $pluralModelLabel = 'Pointages & Présences';

    public static function form(Schema $schema): Schema
    {
        return StaffAttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffAttendancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            StaffAttendanceWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffAttendances::route('/'),
            // 'create' => Pages\CreateStaffAttendance::route('/create'),
            // 'edit' => Pages\EditStaffAttendance::route('/{record}/edit'),
        ];
    }
}
