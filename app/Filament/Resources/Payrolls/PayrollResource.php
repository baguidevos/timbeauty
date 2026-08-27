<?php

namespace App\Filament\Resources\Payrolls;

use App\Filament\Clusters\HumanResources;
use App\Filament\Resources\Payrolls\Schemas\PayrollForm;
use App\Filament\Resources\Payrolls\Tables\PayrollsTable;
use App\Filament\Resources\Payrolls\Widgets\PayrollChartWidget;
use App\Filament\Resources\Payrolls\Widgets\PayrollStatsWidget;
use App\Models\Payroll;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $cluster = HumanResources::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationLabel = 'Fiches de Paie';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Fiche de paie';

    protected static ?string $pluralModelLabel = 'Fiches de paie';

    public static function form(Schema $schema): Schema
    {
        return PayrollForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            PayrollStatsWidget::class,
            PayrollChartWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            // 'create' => Pages\CreatePayroll::route('/create'),
            // 'view' => Pages\ViewPayroll::route('/{record}'),
            // 'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
