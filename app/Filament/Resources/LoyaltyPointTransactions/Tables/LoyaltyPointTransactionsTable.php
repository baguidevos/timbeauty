<?php

namespace App\Filament\Resources\LoyaltyPointTransactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoyaltyPointTransactionsTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('client.firstName')
                    ->label('Client')
                    ->formatStateUsing(fn ($record) => $record->client?->firstName.' '.$record->client?->lastName)
                    ->searchable(),
                TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->color(fn ($record) => $record->type === 'earn' ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'earn' => 'success',
                        'redeem' => 'warning',
                        'expire' => 'danger',
                        'adjust' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'earn' => 'Gagné',
                        'redeem' => 'Utilisé',
                        'expire' => 'Expiré',
                        'adjust' => 'Ajustement',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'earn' => 'Gagné',
                        'redeem' => 'Utilisé',
                        'expire' => 'Expiré',
                        'adjust' => 'Ajustement',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
