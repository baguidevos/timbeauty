<?php

namespace App\Filament\Resources\LoyaltyRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoyaltyRulesTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requiredVisits')
                    ->label('Visites req.')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('service.name')
                    ->label('Prestation')
                    ->searchable(),
                TextColumn::make('discountPercentage')
                    ->label('Remise')
                    ->formatStateUsing(fn ($state) => $state ? $state.'%' : '-')
                    ->sortable(),
                TextColumn::make('cooldownDays')
                    ->label('Refroidissement (j)')
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Active' : 'Inactive')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
