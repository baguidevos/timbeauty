<?php

namespace App\Filament\Resources\AppointmentPhotos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppointmentPhotosTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('appointment.id')
                    ->label('Rendez-vous')
                    ->formatStateUsing(fn ($record) => 'RDV #'.$record->appointmentId)
                    ->sortable(),
                TextColumn::make('client.firstName')
                    ->label('Client')
                    ->formatStateUsing(fn ($record) => $record->client?->firstName.' '.$record->client?->lastName)
                    ->searchable(),
                TextColumn::make('barber.firstName')
                    ->label('Coiffeur')
                    ->formatStateUsing(fn ($record) => $record->barber?->firstName.' '.$record->barber?->lastName)
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'before' ? 'warning' : 'success')
                    ->formatStateUsing(fn (string $state): string => $state === 'before' ? 'Avant' : 'Après'),
                ImageColumn::make('url')
                    ->label('Photo')
                    ->circular(),
                TextColumn::make('caption')
                    ->label('Légende')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'before' => 'Avant',
                        'after' => 'Après',
                    ]),
                Tables\Filters\SelectFilter::make('barberId')
                    ->label('Coiffeur')
                    ->relationship('barber', 'firstName')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName),
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
