<?php

namespace App\Filament\Resources\LoyaltyTiers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoyaltyTiersTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('minPoints')
                    ->label('Points min.')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pointsPerFCFA')
                    ->label('Points/FCFA')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discountPercentage')
                    ->label('Remise')
                    ->formatStateUsing(fn ($state) => $state ? $state.'%' : '-')
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color')
                    ->label('Couleur')
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
                TextColumn::make('clients_count')
                    ->label('Clients')
                    ->counts('clients')
                    ->numeric()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('minPoints', 'asc');
    }
}
