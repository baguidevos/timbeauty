<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Helpers\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('beneficiary')
                    ->label('Bénéficiaire')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('paymentMethod')
                    ->label('Paiement')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Espèces',
                        'card' => 'Carte',
                        'transfer' => 'Virement',
                        'check' => 'Chèque',
                        'other' => 'Autre',
                        default => $state,
                    }),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoryId')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('paymentMethod')
                    ->label('Méthode de paiement')
                    ->options([
                        'cash' => 'Espèces',
                        'card' => 'Carte',
                        'transfer' => 'Virement',
                        'check' => 'Chèque',
                        'other' => 'Autre',
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
            ->defaultSort('date', 'desc');
    }
}
