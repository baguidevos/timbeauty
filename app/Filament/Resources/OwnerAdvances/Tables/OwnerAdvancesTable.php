<?php

namespace App\Filament\Resources\OwnerAdvances\Tables;

use App\Helpers\FormatHelper;
use App\Models\OwnerAdvance;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OwnerAdvancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('N°')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Propriétaire')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('reason')
                    ->label('Motif')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('amount')
                    ->label('Montant apporté')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('refundedAmount')
                    ->label('Remboursé')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('remaining')
                    ->label('Reste dû')
                    ->state(fn (OwnerAdvance $record): float => $record->remaining_amount)
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->color(fn ($state): string => $state > 0 ? 'amber' : 'success')
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'refunded' => 'success',
                        'partially_refunded' => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'refunded' => 'Soldé',
                        'partially_refunded' => 'Partiel',
                        'pending' => 'En attente',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('cashRegister.id')
                    ->label('Caisse N°')
                    ->placeholder('Hors caisse')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date d\'apport')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'partially_refunded' => 'Partiellement remboursé',
                        'refunded' => 'Soldé',
                    ]),
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
            ->defaultSort('created_at', 'desc');
    }
}
