<?php

namespace App\Filament\Resources\LoyaltyPointTransactions;

use App\Filament\Resources\LoyaltyPointTransactions\Schemas\LoyaltyPointTransactionForm;
use App\Filament\Resources\LoyaltyPointTransactions\Tables\LoyaltyPointTransactionsTable;
use App\Models\LoyaltyPointTransaction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LoyaltyPointTransactionResource extends Resource
{
    protected static ?string $model = LoyaltyPointTransaction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 55;

    protected static ?string $modelLabel = 'Transaction de points';

    protected static ?string $pluralModelLabel = 'Transactions de points';

    public static function form(Schema $schema): Schema
    {
        return LoyaltyPointTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyPointTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyPointTransactions::route('/'),
            'create' => Pages\CreateLoyaltyPointTransaction::route('/create'),
            'view' => Pages\ViewLoyaltyPointTransaction::route('/{record}'),
        ];
    }
}
