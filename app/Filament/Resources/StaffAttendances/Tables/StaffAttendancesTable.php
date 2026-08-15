<?php

namespace App\Filament\Resources\StaffAttendances\Tables;

use App\Models\Barber;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StaffAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('barber.firstName')
                    ->label('Employé')
                    ->formatStateUsing(fn ($record) => $record->barber ? "{$record->barber->firstName} {$record->barber->lastName}" : '-')
                    ->description(function ($record) {
                        if (! $record->barber) {
                            return null;
                        }

                        return match ($record->barber->jobTitle) {
                            'barber' => 'Coiffeur',
                            'manager' => 'Gérant',
                            'receptionist' => 'Réceptionniste',
                            'cashier' => 'Caissier',
                            'cleaner' => 'Entretien',
                            default => 'Personnel',
                        };
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('clockIn')
                    ->label('Arrivée')
                    ->time('H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('clockOut')
                    ->label('Départ')
                    ->time('H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('worked_hours_formatted')
                    ->label('Temps de travail')
                    ->badge()
                    ->color('gray')
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('workedMinutes', $direction)),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        'half_day' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'Présent',
                        'late' => 'En retard',
                        'absent' => 'Absent',
                        'half_day' => 'Demi-journée',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'present' => 'Présent',
                        'late' => 'En retard',
                        'absent' => 'Absent',
                        'half_day' => 'Demi-journée',
                    ]),
                Tables\Filters\SelectFilter::make('barberId')
                    ->label('Employé')
                    ->relationship('barber', 'firstName')
                    ->getOptionLabelFromRecordUsing(fn (Barber $record) => "{$record->firstName} {$record->lastName}"),
            ])
            ->recordActions([
                Action::make('clockOutAction')
                    ->label('Pointer départ')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->color('danger')
                    ->visible(fn ($record) => (bool) ($record->clockIn && ! $record->clockOut))
                    ->action(function ($record): void {
                        $now = now();
                        $record->clockOut = $now;
                        $record->workedMinutes = $record->calculateWorkedMinutes();
                        $record->save();

                        Notification::make()
                            ->title('Départ enregistré')
                            ->body("Total travaillé : {$record->worked_hours_formatted}")
                            ->success()
                            ->send();
                    }),

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
