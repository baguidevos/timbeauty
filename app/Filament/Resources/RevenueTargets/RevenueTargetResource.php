<?php

namespace App\Filament\Resources\RevenueTargets;

use App\Filament\Resources\RevenueTargets\Schemas\RevenueTargetForm;
use App\Filament\Resources\RevenueTargets\Tables\RevenueTargetsTable;
use App\Models\RevenueTarget;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RevenueTargetResource extends Resource
{
    protected static ?string $model = RevenueTarget::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 25;

    protected static ?string $modelLabel = 'Objectif de revenu';

    protected static ?string $pluralModelLabel = 'Objectifs de revenus';

    public static function form(Schema $schema): Schema
    {
        return RevenueTargetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RevenueTargetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevenueTargets::route('/'),
            'create' => Pages\CreateRevenueTarget::route('/create'),
            'edit' => Pages\EditRevenueTarget::route('/{record}/edit'),
        ];
    }
}
