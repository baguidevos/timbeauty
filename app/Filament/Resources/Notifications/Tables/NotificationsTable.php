<?php

namespace App\Filament\Resources\Notifications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('client.firstName')
                    ->label('Client')
                    ->formatStateUsing(fn ($record) => $record->client ? $record->client->firstName.' '.$record->client->lastName : '-')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'appointment_reminder' => 'Rappel RDV',
                        'appointment_confirmation' => 'Confirmation RDV',
                        'promotion' => 'Promotion',
                        'loyalty' => 'Fidélité',
                        'general' => 'Général',
                        default => $state,
                    }),
                TextColumn::make('channel')
                    ->label('Canal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sms' => 'SMS',
                        'whatsapp' => 'WhatsApp',
                        'email' => 'Email',
                        'push' => 'Push',
                        default => $state,
                    }),
                TextColumn::make('message')
                    ->label('Message')
                    ->limit(40),
                IconColumn::make('read')
                    ->label('Lu')
                    ->boolean(),
                IconColumn::make('sent')
                    ->label('Envoyé')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('read')
                    ->label('Lu'),
                Tables\Filters\TernaryFilter::make('sent')
                    ->label('Envoyé'),
                Tables\Filters\SelectFilter::make('channel')
                    ->label('Canal')
                    ->options([
                        'sms' => 'SMS',
                        'whatsapp' => 'WhatsApp',
                        'email' => 'Email',
                        'push' => 'Push',
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
