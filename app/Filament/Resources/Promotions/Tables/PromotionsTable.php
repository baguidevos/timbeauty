<?php

namespace App\Filament\Resources\Promotions\Tables;

use App\Models\Promotion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Promotion')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(function (Promotion $record) {
                        $count = $record->services()->count();

                        return $count > 0 ? "{$count} prestation(s) éligible(s)" : 'Toutes prestations';
                    }),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'warning' => 'percentage',
                        'success' => 'fixed',
                        'primary' => 'free_service',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Pourcentage',
                        'fixed' => 'Montant fixe',
                        'free_service' => 'Prestation offerte',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('formatted_value')
                    ->label('Remise')
                    ->badge()
                    ->color('gray')
                    ->weight('bold'),

                TextColumn::make('dynamic_status')
                    ->label('État')
                    ->badge()
                    ->state(fn (Promotion $record): string => $record->dynamic_status_label)
                    ->color(fn (Promotion $record): string => $record->dynamic_status_color),

                TextColumn::make('period')
                    ->label('Période de validité')
                    ->state(function (Promotion $record): string {
                        if (! $record->startDate && ! $record->endDate) {
                            return 'Permanente';
                        }
                        $start = $record->startDate ? $record->startDate->format('d/m/Y') : 'Toujours';
                        $end = $record->endDate ? $record->endDate->format('d/m/Y') : 'Sans limite';

                        return "{$start} — {$end}";
                    })
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('startDate', $direction)),

                TextColumn::make('usage_ratio')
                    ->label('Utilisations')
                    ->badge()
                    ->color('gray')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('currentUsages', $direction)),

                IconColumn::make('forLoyalOnly')
                    ->label('Fidèles')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'percentage' => 'Pourcentage',
                        'fixed' => 'Montant fixe',
                        'free_service' => 'Prestation offerte',
                    ]),
                Tables\Filters\TernaryFilter::make('forLoyalOnly')
                    ->label('Clients fidèles uniquement'),
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
            ->defaultSort('created_at', 'desc');
    }
}
