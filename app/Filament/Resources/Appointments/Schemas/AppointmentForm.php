<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Helpers\FormatHelper;
use App\Models\Barber;
use App\Models\Client;
use App\Models\Service;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AppointmentForm
{
    public static function calculateEndTime(Get $get, Set $set): void
    {
        $startTime = $get('startTime');
        $serviceId = $get('serviceId');

        if (! $startTime || ! $serviceId) {
            return;
        }

        $service = Service::find($serviceId);
        if ($service && $service->duration > 0) {
            try {
                $start = Carbon::createFromFormat('H:i', substr((string) $startTime, 0, 5));
                $end = $start->copy()->addMinutes((int) $service->duration);
                $set('endTime', $end->format('H:i'));
            } catch (\Throwable) {
                // Ignore parsing errors on partial inputs
            }
        }
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('AppointmentTabs')
                    ->tabs([
                        Tab::make('Client & Prestation')
                            ->icon(Heroicon::OutlinedUser)
                            ->schema([
                                Select::make('clientId')
                                    ->label('Client')
                                    ->relationship('client', 'firstName')
                                    ->getOptionLabelFromRecordUsing(fn (Client $record) => "{$record->firstName} {$record->lastName}".($record->code ? " [{$record->code}]" : '').($record->phone ? " ({$record->phone})" : ''))
                                    ->searchable(['code', 'firstName', 'lastName', 'phone'])
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('firstName')
                                            ->label('Prénom')
                                            ->required()
                                            ->maxLength(50),
                                        TextInput::make('lastName')
                                            ->label('Nom')
                                            ->required()
                                            ->maxLength(50),
                                        TextInput::make('phone')
                                            ->label('Téléphone')
                                            ->tel()
                                            ->maxLength(20),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        return Client::create($data)->id;
                                    }),

                                Select::make('barberId')
                                    ->label('Coiffeur / Personnel')
                                    ->relationship(
                                        'barber',
                                        'firstName',
                                        fn ($query) => $query->where('canPerformServices', true)->where('status', 'active')
                                    )
                                    ->getOptionLabelFromRecordUsing(function (Barber $record) {
                                        $jobLabel = match ($record->jobTitle) {
                                            'barber' => 'Coiffeur',
                                            'manager' => 'Gérant',
                                            'receptionist' => 'Réceptionniste',
                                            default => 'Personnel',
                                        };

                                        return "{$record->firstName} {$record->lastName} ({$jobLabel})";
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('serviceId')
                                    ->label('Prestation demandée')
                                    ->relationship('service', 'name', fn ($query) => $query->where('status', 'active'))
                                    ->getOptionLabelFromRecordUsing(fn (Service $record) => "{$record->name} ({$record->duration} min - ".FormatHelper::formatFCFA($record->price).')')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => static::calculateEndTime($get, $set))
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Date & Horaire')
                            ->icon(Heroicon::OutlinedClock)
                            ->schema([
                                DatePicker::make('date')
                                    ->label('Date du rendez-vous')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->columnSpanFull(),

                                TimePicker::make('startTime')
                                    ->label('Heure de début')
                                    ->required()
                                    ->seconds(false)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => static::calculateEndTime($get, $set)),

                                TimePicker::make('endTime')
                                    ->label('Heure de fin estimée')
                                    ->required()
                                    ->seconds(false),
                            ])
                            ->columns(2),

                        Tab::make('Statut & Notes')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                ToggleButtons::make('status')
                                    ->label('Statut du rendez-vous')
                                    ->options([
                                        'pending' => 'En attente',
                                        'confirmed' => 'Confirmé',
                                        'in_progress' => 'En cours',
                                        'completed' => 'Terminé',
                                        'cancelled' => 'Annulé',
                                        'no_show' => 'Absent',
                                    ])
                                    ->colors([
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'in_progress' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        'no_show' => 'danger',
                                    ])
                                    ->icons([
                                        'pending' => 'heroicon-o-clock',
                                        'confirmed' => 'heroicon-o-check-circle',
                                        'in_progress' => 'heroicon-o-play',
                                        'completed' => 'heroicon-o-check',
                                        'cancelled' => 'heroicon-o-x-mark',
                                        'no_show' => 'heroicon-o-user-minus',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->inline()
                                    ->columnSpanFull(),

                                Textarea::make('notes')
                                    ->label('Notes complémentaires ou demandes spécifiques')
                                    ->placeholder('Ex: Préférence de coupe, demande de shampoing...')
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
