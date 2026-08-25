<?php

namespace App\Filament\Resources\BankDeposits\Tables;

use App\Helpers\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BankDepositsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('bankName')
                    ->label('Banque')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('amount')
                    ->label('Montant déposé')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->weight('bold')
                    ->color('emerald')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('depositSlipNumber')
                    ->label('N° Bordereau')
                    ->searchable()
                    ->placeholder('—'),

                ImageColumn::make('depositSlipPhoto')
                    ->label('Reçu')
                    ->circular(),

                TextColumn::make('courier.name')
                    ->label('Déposé par')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => 'Confirmé',
                        'pending' => 'En cours',
                        'cancelled' => 'Annulé',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('cashRegister.id')
                    ->label('Caisse N°')
                    ->sortable(),

                TextColumn::make('depositDate')
                    ->label('Date de dépôt')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'confirmed' => 'Confirmé',
                        'pending' => 'En cours',
                        'cancelled' => 'Annulé',
                    ]),
                SelectFilter::make('bankName')
                    ->label('Banque'),
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
            ->defaultSort('depositDate', 'desc');
    }
}
