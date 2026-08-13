<?php

namespace App\Filament\Resources\CashRegisters\Tables;

use App\Helpers\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CashRegistersTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('N°')
                    ->sortable(),
                TextColumn::make('openingAmount')
                    ->label('Ouverture')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('closingAmount')
                    ->label('Clôture')
                    ->formatStateUsing(fn ($state) => $state ? FormatHelper::formatFCFA($state) : '-')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Ouverte',
                        'closed' => 'Fermée',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('opener.name')
                    ->label('Ouvert par')
                    ->searchable(),
                TextColumn::make('openedAt')
                    ->label('Ouverte le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('closedAt')
                    ->label('Fermée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'open' => 'Ouverte',
                        'closed' => 'Fermée',
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
            ->defaultSort('openedAt', 'desc');
    }
}
