<?php

namespace App\Filament\Resources\Appointments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.firstName')
                    ->label('Client')
                    ->formatStateUsing(fn ($record) => $record->client?->firstName.' '.$record->client?->lastName)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('barber.firstName')
                    ->label('Coiffeur')
                    ->formatStateUsing(fn ($record) => $record->barber?->firstName.' '.$record->barber?->lastName)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service.name')
                    ->label('Prestation')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('startTime')
                    ->label('Heure début')
                    ->sortable(),
                TextColumn::make('endTime')
                    ->label('Heure fin')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmé',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        'no_show' => 'Non présenté',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmé',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        'no_show' => 'Non présenté',
                    ]),
                Tables\Filters\SelectFilter::make('barberId')
                    ->label('Coiffeur')
                    ->relationship('barber', 'firstName')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName),
                Tables\Filters\SelectFilter::make('serviceId')
                    ->label('Prestation')
                    ->relationship('service', 'name'),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->label('Confirmer')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(fn ($record) => $record->update(['status' => 'confirmed'])),
                Action::make('start')
                    ->label('Démarrer')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'confirmed')
                    ->action(fn ($record) => $record->update(['status' => 'in_progress'])),
                Action::make('complete')
                    ->label('Terminer')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'in_progress')
                    ->action(fn ($record) => $record->update(['status' => 'completed'])),
                Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => ! in_array($record->status, ['completed', 'cancelled']))
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'cancelled'])),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
