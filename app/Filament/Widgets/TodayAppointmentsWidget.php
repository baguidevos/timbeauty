<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->where('date', now()->toDateString())
                    ->with(['client', 'barber', 'service'])
                    ->orderBy('startTime')
            )
            ->heading('Rendez-vous d\'aujourd\'hui')
            ->emptyStateHeading('Aucun rendez-vous aujourd\'hui')
            ->emptyStateDescription('Les rendez-vous du jour apparaîtront ici.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->columns([
                Tables\Columns\TextColumn::make('startTime')
                    ->label('Heure')
                    ->time('H:i')
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('client.fullName')
                    ->label('Client')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('barber.fullName')
                    ->label('Coiffeur')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
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
                        'no_show' => 'Absent',
                        default => $state,
                    }),
            ]);
    }
}
