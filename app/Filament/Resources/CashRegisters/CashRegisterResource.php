<?php

namespace App\Filament\Resources\CashRegisters;

use App\Filament\Resources\CashRegisters\Schemas\CashRegisterForm;
use App\Filament\Resources\CashRegisters\Tables\CashRegistersTable;
use App\Models\CashRegister;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wallet';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Caisse';

    protected static ?string $pluralModelLabel = 'Caisses';

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
