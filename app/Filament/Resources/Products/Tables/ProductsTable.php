<?php

namespace App\Filament\Resources\Products\Tables;

use App\Helpers\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('sellingPrice')
                    ->label('Prix de vente')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('stockQuantity')
                    ->label('Stock')
                    ->badge()
                    ->icon(fn ($record) => $record->stockQuantity <= $record->minStockLevel ? 'heroicon-o-exclamation-triangle' : null)
                    ->color(fn ($record) => $record->stockQuantity <= $record->minStockLevel ? 'danger' : 'success')
                    ->extraAttributes(fn ($record) => $record->stockQuantity <= $record->minStockLevel ? ['class' => 'animate-pulse font-bold'] : [])
                    ->extraCellAttributes(fn ($record) => $record->stockQuantity <= $record->minStockLevel ? ['class' => 'animate-pulse'] : [])
                    ->numeric()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('supplier.name')
                    ->label('Fournisseur')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock bas uniquement')
                    ->query(fn (Builder $query): Builder => $query->lowStock())
                    ->toggle(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                    ]),
                Tables\Filters\SelectFilter::make('categoryId')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('supplierId')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->slideOver(),
                EditAction::make()
                    ->slideOver(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
