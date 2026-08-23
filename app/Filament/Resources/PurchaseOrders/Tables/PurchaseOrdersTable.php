<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
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
                    ->color('warning')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Fournisseur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('orderDate')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('totalAmount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state ?? 0))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('paidAmount')
                    ->label('Payé')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state ?? 0))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'ordered' => 'heroicon-o-paper-airplane',
                        'partially_received' => 'heroicon-o-cube',
                        'received' => 'heroicon-o-check-circle',
                        'cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-information-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'ordered' => 'info',
                        'partially_received' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'ordered' => 'Envoyée',
                        'partially_received' => 'Partiellement reçue',
                        'received' => 'Reçue',
                        'cancelled' => 'Annulée',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->searchPlaceholder('Rechercher une commande...')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'ordered' => 'Envoyée',
                        'partially_received' => 'Partiellement reçue',
                        'received' => 'Reçue',
                        'cancelled' => 'Annulée',
                    ]),
                Tables\Filters\SelectFilter::make('supplierId')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name'),
            ])
            ->recordUrl(fn ($record) => PurchaseOrderResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => PurchaseOrderResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('orderDate', 'desc');
    }
}
