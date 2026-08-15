<?php

namespace App\Filament\Resources\Suppliers\Tables;

use App\Helpers\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contactName')
                    ->label('Contact')
                    ->searchable()
                    ->default('-')
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->default('-'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->default('-')
                    ->toggleable(),
                TextColumn::make('paymentTerms')
                    ->label('Conditions')
                    ->searchable()
                    ->default('-')
                    ->toggleable(),
                TextColumn::make('purchase_orders_count')
                    ->label('Cmds')
                    ->counts('purchaseOrders')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('purchase_orders_sum_total_amount')
                    ->label('Montant total')
                    ->sum('purchaseOrders', 'totalAmount')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state ?? 0))
                    ->sortable()
                    ->alignEnd(),
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
            ->searchPlaceholder('Rechercher un fournisseur...')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                    ]),
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
