<?php

namespace App\Filament\Resources\StaffAbsences\Schemas;

use App\Models\Barber;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StaffAbsenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Détails de l\'absence / congé')
                    ->schema([
                        Select::make('barberId')
                            ->label('Employé')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(function (Barber $record) {
                                $jobLabel = match ($record->jobTitle) {
                                    'barber' => 'Coiffeur',
                                    'manager' => 'Gérant',
                                    'receptionist' => 'Réceptionniste',
                                    'cashier' => 'Caissier',
                                    'cleaner' => 'Entretien',
                                    default => 'Personnel',
                                };

                                return "{$record->firstName} {$record->lastName} ({$jobLabel})";
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        ToggleButtons::make('type')
                            ->label('Type d\'absence')
                            ->options([
                                'vacation' => 'Congé payé',
                                'sick' => 'Maladie',
                                'personal' => 'Personnel',
                                'absence' => 'Absence injustifiée',
                            ])
                            ->colors([
                                'vacation' => 'info',
                                'sick' => 'warning',
                                'personal' => 'primary',
                                'absence' => 'danger',
                            ])
                            ->icons([
                                'vacation' => 'heroicon-o-sun',
                                'sick' => 'heroicon-o-heart',
                                'personal' => 'heroicon-o-user',
                                'absence' => 'heroicon-o-x-circle',
                            ])
                            ->default('vacation')
                            ->required()
                            ->inline()
                            ->columnSpanFull(),

                        DatePicker::make('startDate')
                            ->label('Date de début')
                            ->default(now())
                            ->required()
                            ->live()
                            ->native(false),

                        DatePicker::make('endDate')
                            ->label('Date de fin')
                            ->default(now())
                            ->required()
                            ->live()
                            ->native(false)
                            ->helperText(function (Get $get) {
                                $start = $get('startDate');
                                $end = $get('endDate');
                                if (! $start || ! $end) {
                                    return null;
                                }

                                try {
                                    $s = Carbon::parse($start);
                                    $e = Carbon::parse($end);
                                    if ($e->lt($s)) {
                                        return '⚠️ La date de fin doit être postérieure à la date de début';
                                    }
                                    $days = $s->diffInDays($e) + 1;

                                    return "⏱️ Durée de l'absence : {$days} jour(s)";
                                } catch (\Throwable) {
                                    return null;
                                }
                            }),

                        Textarea::make('reason')
                            ->label('Motif & Justificatif (optionnel)')
                            ->placeholder('Ex: Vacances d\'été, arrêt maladie avec certificat médical...')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
