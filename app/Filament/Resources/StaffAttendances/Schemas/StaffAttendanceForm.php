<?php

namespace App\Filament\Resources\StaffAttendances\Schemas;

use App\Models\Barber;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class StaffAttendanceForm
{
    public static function calculateWorkedMinutes(Get $get, Set $set): void
    {
        $clockIn = $get('clockIn');
        $clockOut = $get('clockOut');
        $breakMinutes = (int) ($get('breakMinutes') ?? 0);

        if (! $clockIn || ! $clockOut) {
            return;
        }

        try {
            $start = Carbon::parse($clockIn);
            $end = Carbon::parse($clockOut);
            $diff = max(0, $start->diffInMinutes($end));
            $net = max(0, $diff - $breakMinutes);

            $set('workedMinutes', $net);
        } catch (\Throwable) {
            // Ignore parsing errors
        }
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('AttendanceTabs')
                    ->tabs([
                        Tab::make('Employé & Date')
                            ->icon(Heroicon::OutlinedUser)
                            ->schema([
                                Select::make('barberId')
                                    ->label('Membre du personnel')
                                    ->relationship('barber', 'firstName')
                                    ->getOptionLabelFromRecordUsing(function (Barber $record) {
                                        $jobLabel = match ($record->jobTitle) {
                                            'barber' => 'Coiffeur',
                                            'manager' => 'Gérant',
                                            'receptionist' => 'Réceptionniste',
                                            'cashier' => 'Caissier',
                                            'cleaner' => 'Entretien',
                                            default => 'Employé',
                                        };

                                        return "{$record->firstName} {$record->lastName} ({$jobLabel})";
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                DatePicker::make('date')
                                    ->label('Date de présence')
                                    ->required()
                                    ->default(now())
                                    ->native(false),
                            ])
                            ->columns(2),

                        Tab::make('Horaires & Pointage')
                            ->icon(Heroicon::OutlinedClock)
                            ->schema([
                                DateTimePicker::make('clockIn')
                                    ->label('Heure d\'arrivée (Pointage entrée)')
                                    ->seconds(false)
                                    ->native(false)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => static::calculateWorkedMinutes($get, $set)),

                                DateTimePicker::make('clockOut')
                                    ->label('Heure de départ (Pointage sortie)')
                                    ->seconds(false)
                                    ->native(false)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => static::calculateWorkedMinutes($get, $set)),

                                TextInput::make('breakMinutes')
                                    ->label('Temps de pause (minutes)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => static::calculateWorkedMinutes($get, $set)),

                                TextInput::make('workedMinutes')
                                    ->label('Minutes travaillées effectives')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Calculé automatiquement : (Départ - Arrivée) - Pause'),
                            ])
                            ->columns(2),

                        Tab::make('Statut & Notes')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                ToggleButtons::make('status')
                                    ->label('Statut de la présence')
                                    ->options([
                                        'present' => 'Présent',
                                        'late' => 'En retard',
                                        'absent' => 'Absent',
                                        'half_day' => 'Demi-journée',
                                    ])
                                    ->colors([
                                        'present' => 'success',
                                        'late' => 'warning',
                                        'absent' => 'danger',
                                        'half_day' => 'info',
                                    ])
                                    ->icons([
                                        'present' => 'heroicon-o-check-circle',
                                        'late' => 'heroicon-o-clock',
                                        'absent' => 'heroicon-o-x-circle',
                                        'half_day' => 'heroicon-o-sun',
                                    ])
                                    ->default('present')
                                    ->required()
                                    ->inline()
                                    ->columnSpanFull(),

                                Textarea::make('notes')
                                    ->label('Notes & Justificatifs')
                                    ->placeholder('Ex: Justificatif médical, retard justifié...')
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
