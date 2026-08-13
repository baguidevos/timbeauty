<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->default('-'),
                TextColumn::make('action')
                    ->label('Action')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('entity')
                    ->label('Entité')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('entityId')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('details')
                    ->label('Détails')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Action')
                    ->options(fn () => ActivityLog::query()->distinct()->pluck('action', 'action')->toArray()),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
