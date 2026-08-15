<?php

namespace App\Filament\Resources\Barbers\RelationManagers;

use App\Filament\Resources\StaffAbsences\Schemas\StaffAbsenceForm;
use App\Models\StaffAbsence;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AbsencesRelationManager extends RelationManager
{
    protected static string $relationship = 'absences';

    protected static ?string $title = 'Congés & Absences';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-sun';

    public function form(Schema $schema): Schema
    {
        return StaffAbsenceForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vacation', 'leave' => 'info',
                        'sick', 'sick_leave' => 'warning',
                        'personal' => 'primary',
                        'absence' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'vacation', 'leave' => 'Congé payé',
                        'sick', 'sick_leave' => 'Maladie',
                        'personal' => 'Personnel',
                        'absence' => 'Absence injustifiée',
                        default => $state,
                    }),

                TextColumn::make('startDate')
                    ->label('Période')
                    ->formatStateUsing(fn (StaffAbsence $record) => $record->startDate->format('d/m/Y').' — '.$record->endDate->format('d/m/Y')),

                TextColumn::make('days_count')
                    ->label('Durée')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => "{$state} j"),

                TextColumn::make('temporal_status')
                    ->label('État')
                    ->badge()
                    ->state(function (StaffAbsence $record): string {
                        $today = now()->startOfDay();
                        if ($today->lt($record->startDate)) {
                            return 'À venir';
                        }
                        if ($today->gt($record->endDate)) {
                            return 'Terminé';
                        }

                        return 'En cours';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'En cours' => 'danger',
                        'À venir' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('reason')
                    ->label('Motif')
                    ->limit(25),
            ])
            ->headerActions([
                CreateAction::make()->slideOver(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->defaultSort('startDate', 'desc');
    }
}
