<?php

namespace App\Filament\Resources\LoyaltyRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->label('Règle de fidélité')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('requiredVisits')
                    ->label('Palier requis')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-m-sparkles')
                    ->formatStateUsing(fn ($state) => "{$state} visites")
                    ->sortable(),

                TextColumn::make('discountPercentage')
                    ->label('Avantage accordé')
                    ->badge()
                    ->color(fn ($state) => (float) $state >= 100 ? 'primary' : 'success')
                    ->formatStateUsing(fn ($state) => (float) $state >= 100 ? '100% Offert' : "-{$state}%")
                    ->sortable(),

                TextColumn::make('service.name')
                    ->label('Prestation cible')
                    ->placeholder('Toutes prestations')
                    ->searchable(),

                TextColumn::make('validityDays')
                    ->label('Validité')
                    ->formatStateUsing(fn ($state) => "{$state} j")
                    ->sortable(),

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
                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('requiredVisits', 'asc');
    }
}
