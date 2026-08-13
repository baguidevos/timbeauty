<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatHelper;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlertWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->whereColumn('stockQuantity', '<=', 'minStockLevel')
                    ->where('status', 'active')
            )
            ->heading('⚠️ Alerte stock bas')
            ->emptyStateHeading('Stock OK')
            ->emptyStateDescription('Tous les produits sont au-dessus du seuil minimum.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produit')
                    ->searchable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('stockQuantity')
                    ->label('Stock actuel')
                    ->numeric()
                    ->color('danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('minStockLevel')
                    ->label('Stock min.')
                    ->numeric()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('sellingPrice')
                    ->label('Prix')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state)),
            ])
            ->defaultSort('stockQuantity', 'asc');
    }
}
