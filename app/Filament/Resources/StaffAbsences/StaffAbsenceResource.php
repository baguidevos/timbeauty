<?php

namespace App\Filament\Resources\StaffAbsences;

use App\Filament\Clusters\HumanResources;
use App\Filament\Resources\StaffAbsences\Schemas\StaffAbsenceForm;
use App\Filament\Resources\StaffAbsences\Tables\StaffAbsencesTable;
use App\Models\StaffAbsence;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StaffAbsenceResource extends Resource
{
    protected static ?string $model = StaffAbsence::class;

    protected static ?string $cluster = HumanResources::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sun';

    protected static ?string $navigationLabel = 'Congés & Absences';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Absence / Congé';

    protected static ?string $pluralModelLabel = 'Congés & Absences';

    public static function form(Schema $schema): Schema
    {
        return StaffAbsenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffAbsencesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffAbsences::route('/'),
        ];
    }
}
