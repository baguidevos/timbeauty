<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Helpers\FormatHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
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
                    ->formatStateUsing(fn ($record) => $record->client ? "{$record->client->firstName} {$record->client->lastName}" : '-')
                    ->description(fn ($record) => $record->client?->phone)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('client', function ($q) use ($search) {
                            $q->where('firstName', 'like', "%{$search}%")
                                ->orWhere('lastName', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                TextColumn::make('barber.firstName')
                    ->label('Coiffeur')
                    ->formatStateUsing(fn ($record) => $record->barber ? "{$record->barber->firstName} {$record->barber->lastName}" : '-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service.name')
                    ->label('Prestation')
                    ->description(fn ($record) => $record->service ? "{$record->service->duration} min • ".FormatHelper::formatFCFA($record->service->price) : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('startTime')
                    ->label('Créneau')
                    ->formatStateUsing(fn ($record) => substr((string) $record->startTime, 0, 5).' - '.substr((string) $record->endTime, 0, 5))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'in_progress' => 'primary',
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
                        'no_show' => 'Absent',
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
                        'no_show' => 'Absent',
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
                    ->action(function ($record, $livewire): void {
                        $record->update(['status' => 'confirmed']);
                        Notification::make()->title('Rendez-vous confirmé')->success()->send();
                        $livewire->dispatch('refreshAppointmentPlanner');
                    }),

                Action::make('start')
                    ->label('Démarrer')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->visible(fn ($record) => $record->status === 'confirmed')
                    ->action(function ($record, $livewire): void {
                        $record->update(['status' => 'in_progress']);
                        Notification::make()->title('Prestation en cours')->success()->send();
                        $livewire->dispatch('refreshAppointmentPlanner');
                    }),

                Action::make('complete')
                    ->label('Terminer')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'in_progress')
                    ->action(function ($record, $livewire): void {
                        $record->update(['status' => 'completed']);
                        Notification::make()->title('Rendez-vous terminé')->success()->send();
                        $livewire->dispatch('refreshAppointmentPlanner');
                    }),

                Action::make('no_show')
                    ->label('Absent')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed']))
                    ->requiresConfirmation()
                    ->modalHeading('Marquer le client comme non présenté ?')
                    ->action(function ($record, $livewire): void {
                        $record->update(['status' => 'no_show']);
                        Notification::make()->title('Statut mis à jour : Absent')->warning()->send();
                        $livewire->dispatch('refreshAppointmentPlanner');
                    }),

                Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => ! in_array($record->status, ['completed', 'cancelled', 'no_show']))
                    ->requiresConfirmation()
                    ->modalHeading('Annuler ce rendez-vous ?')
                    ->action(function ($record, $livewire): void {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()->title('Rendez-vous annulé')->warning()->send();
                        $livewire->dispatch('refreshAppointmentPlanner');
                    }),

                ViewAction::make()
                    ->slideOver(),
                EditAction::make()
                    ->slideOver()
                    ->after(function ($livewire): void {
                        $livewire->dispatch('refreshAppointmentPlanner');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function ($livewire): void {
                            $livewire->dispatch('refreshAppointmentPlanner');
                        }),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
