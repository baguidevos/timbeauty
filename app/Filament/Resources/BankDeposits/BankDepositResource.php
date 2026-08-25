<?php

namespace App\Filament\Resources\BankDeposits;

use App\Filament\Resources\BankDeposits\Pages\ListBankDeposits;
use App\Filament\Resources\BankDeposits\Schemas\BankDepositForm;
use App\Filament\Resources\BankDeposits\Tables\BankDepositsTable;
use App\Models\BankDeposit;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BankDepositResource extends Resource
{
    protected static ?string $model = BankDeposit::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 41;

    protected static ?string $modelLabel = 'Dépôt en Banque';

    protected static ?string $pluralModelLabel = 'Dépôts en Banque';

    public static function form(Schema $schema): Schema
    {
        return BankDepositForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankDepositsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBankDeposits::route('/'),
        ];
    }
}
