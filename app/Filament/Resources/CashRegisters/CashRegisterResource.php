<?php

namespace App\Filament\Resources\CashRegisters;

use App\Filament\Clusters\FinanceCluster;
use App\Filament\Resources\CashRegisters\Schemas\CashRegisterForm;
use App\Filament\Resources\CashRegisters\Tables\CashRegistersTable;
use App\Models\CashRegister;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static ?string $cluster = FinanceCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Sessions de Caisse';

    protected static ?string $modelLabel = 'Session de Caisse';

    protected static ?string $pluralModelLabel = 'Sessions de Caisse';

    public static function form(Schema $schema): Schema
    {
        return CashRegisterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashRegistersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CashTransactionsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\CashRegisterStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashRegisters::route('/'),
            'create' => Pages\CreateCashRegister::route('/create'),
            'view' => Pages\ViewCashRegister::route('/{record}'),
            'edit' => Pages\EditCashRegister::route('/{record}/edit'),
        ];
    }
}
