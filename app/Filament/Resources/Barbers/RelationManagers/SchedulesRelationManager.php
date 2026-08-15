<?php

namespace App\Filament\Resources\Barbers\RelationManagers;

use App\Models\StaffSchedule;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Emploi du temps hebdomadaire';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-calendar-days';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('dayOfWeek')
                    ->label('Jour de la semaine')
                    ->options([
                        1 => 'Lundi',
                        2 => 'Mardi',
                        3 => 'Mercredi',
                        4 => 'Jeudi',
                        5 => 'Vendredi',
                        6 => 'Samedi',
                        0 => 'Dimanche',
                    ])
                    ->required(),

                TimePicker::make('startTime')
                    ->label('Heure de début')
                    ->seconds(false)
                    ->default('09:00'),

                TimePicker::make('endTime')
                    ->label('Heure de fin')
                    ->seconds(false)
                    ->default('19:00'),

                Checkbox::make('isDayOff')
                    ->label('Jour de repos')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('dayOfWeek')
            ->columns([
                TextColumn::make('day_name')
                    ->label('Jour')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('dayOfWeek', $direction)),

                TextColumn::make('formatted_hours')
                    ->label('Horaires de travail')
                    ->badge()
                    ->color(fn ($state) => $state === 'Repos' ? 'danger' : 'success'),

                TextColumn::make('isDayOff')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Repos' : 'En service')
                    ->color(fn (bool $state) => $state ? 'danger' : 'success'),
            ])
            ->headerActions([
                Action::make('generateStandardWeek')
                    ->label('Générer la semaine standard (Lun-Sam 9h-19h)')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (): void {
                        $barber = $this->getOwnerRecord();

                        foreach ([1, 2, 3, 4, 5, 6, 0] as $d) {
                            $isOff = ($d === 0); // Dimanche off
                            StaffSchedule::updateOrCreate(
                                ['barberId' => $barber->id, 'dayOfWeek' => $d],
                                [
                                    'startTime' => '09:00',
                                    'endTime' => '19:00',
                                    'isDayOff' => $isOff,
                                ]
                            );
                        }

                        Notification::make()
                            ->title('Semaine standard générée avec succès')
                            ->success()
                            ->send();
                    }),
                CreateAction::make()->slideOver(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->defaultSort('dayOfWeek', 'asc');
    }
}
