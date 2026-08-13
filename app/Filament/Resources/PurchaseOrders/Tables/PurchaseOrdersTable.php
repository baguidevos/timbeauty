<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Helpers\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Fournisseur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('orderDate')
                    ->label('Date de commande')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('expectedDate')
                    ->label('Date prévue')
                    ->date('d/m/Y')
                    ->toggleable(),
                TextColumn::make('totalAmount')
                    ->label('Montant total')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'ordered' => 'info',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'ordered' => 'Commandée',
                        'received' => 'Reçue',
                        'cancelled' => 'Annulée',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'ordered' => 'Commandée',
                        'received' => 'Reçue',
                        'cancelled' => 'Annulée',
                    ]),
                Tables\Filters\SelectFilter::make('supplierId')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('orderDate', 'desc');
    }
}
