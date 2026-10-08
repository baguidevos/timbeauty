<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Helpers\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('firstName')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lastName')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('totalVisits')
                    ->label('Visites')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('firstVisitDate')
                    ->label('Premier visite')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('lastVisitDate')
                    ->label('Dernière visite')
                    ->default(fn ($record) => $record->lastVisitDate ?? 'N/D')
                    ->sortable(),
                TextColumn::make('totalSpent')
                    ->label('Total dépensé')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                IconColumn::make('isLoyal')
                    ->label('Fidèle')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('loyaltyPoints')
                    ->label('Points')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('isLoyal')
                    ->label('Client fidèle'),
                Tables\Filters\SelectFilter::make('loyaltyTierId')
                    ->label('Niveau de fidélité')
                    ->relationship('loyaltyTier', 'name'),
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
