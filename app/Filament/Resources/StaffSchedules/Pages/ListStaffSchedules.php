<?php

namespace App\Filament\Resources\StaffSchedules\Pages;

use App\Filament\Resources\StaffSchedules\StaffScheduleResource;
use App\Models\Barber;
use App\Models\StaffSchedule;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListStaffSchedules extends ListRecords
{
    protected static string $resource = StaffScheduleResource::class;

    protected string $view = 'filament.resources.staff-schedules.pages.list-staff-schedules';

    public string $selectedBarberId = 'all';

    public function getTitle(): string|Htmlable
    {
        return 'Planning & Emplois du temps';
    }

    public function getHeading(): Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Planning & Emplois du temps</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Configuration des horaires hebdomadaires et jours de repos du personnel</span>
                </div>
            </div>
        ');
    }

    public function getBarbersListProperty(): array
    {
        return Barber::where('status', '!=', 'inactive')
            ->get()
            ->map(fn ($b) => [
                'id' => (string) $b->id,
                'name' => "{$b->firstName} {$b->lastName}",
            ])
            ->toArray();
    }

    public function getScheduleMatrixProperty(): array
    {
        $query = Barber::where('status', '!=', 'inactive')->with(['schedules']);

        if ($this->selectedBarberId !== 'all') {
            $query->where('id', $this->selectedBarberId);
        }

        $barbers = $query->get();

        $matrix = [];
        foreach ($barbers as $barber) {
            $jobLabel = match ($barber->jobTitle) {
                'barber' => 'Coiffeur',
                'manager' => 'Gérant',
                'receptionist' => 'Réceptionniste',
                'cashier' => 'Caissier',
                'cleaner' => 'Entretien',
                default => 'Personnel',
            };

            $days = [];
            foreach ($barber->schedules as $sch) {
                $days[$sch->dayOfWeek] = [
                    'id' => $sch->id,
                    'start_time' => $sch->startTime ? substr((string) $sch->startTime, 0, 5) : '09:00',
                    'end_time' => $sch->endTime ? substr((string) $sch->endTime, 0, 5) : '19:00',
                    'is_day_off' => (bool) $sch->isDayOff,
                ];
            }

            $matrix[] = [
                'id' => $barber->id,
                'name' => "{$barber->firstName} {$barber->lastName}",
                'job_title' => $jobLabel,
                'status' => $barber->status,
                'photo' => $barber->photo,
                'days' => $days,
            ];
        }

        return $matrix;
    }

    public function toggleDayOff(int $barberId, int $dayOfWeek): void
    {
        $schedule = StaffSchedule::firstOrNew([
            'barberId' => $barberId,
            'dayOfWeek' => $dayOfWeek,
        ]);

        if (! $schedule->exists) {
            $schedule->startTime = '09:00';
            $schedule->endTime = '19:00';
            $schedule->isDayOff = false;
        } else {
            $schedule->isDayOff = ! $schedule->isDayOff;
            if (! $schedule->startTime || ! $schedule->endTime) {
                $schedule->startTime = '09:00';
                $schedule->endTime = '19:00';
            }
        }

        $schedule->save();

        Notification::make()
            ->title('Horaire mis à jour')
            ->success()
            ->send();
    }

    public function quickSetHours(int $barberId, int $dayOfWeek): void
    {
        StaffSchedule::updateOrCreate(
            ['barberId' => $barberId, 'dayOfWeek' => $dayOfWeek],
            [
                'startTime' => '09:00',
                'endTime' => '19:00',
                'isDayOff' => false,
            ]
        );

        Notification::make()
            ->title('Horaire défini : 09:00 - 19:00')
            ->success()
            ->send();
    }

    public function editBarberWeek(int $barberId): void
    {
        $this->mountAction('configureWeek', ['barberId' => $barberId]);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->configureWeekAction(),
        ];
    }

    public function configureWeekAction(): Action
    {
        $daysConfig = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            0 => 'Dimanche',
        ];

        return Action::make('configureWeek')
            ->label('Configurer la semaine')
            ->icon('heroicon-o-calendar-days')
            ->color('warning')
            ->modalHeading('Configurer les horaires hebdomadaires')
            ->modalDescription('Définissez les heures de travail et jours de repos pour chaque jour de la semaine.')
            ->modalWidth(Width::FourExtraLarge)
            ->fillForm(function (array $arguments): array {
                $barberId = $arguments['barberId'] ?? null;
                $data = ['barberId' => $barberId];

                if ($barberId) {
                    $schedules = StaffSchedule::where('barberId', $barberId)->get()->keyBy('dayOfWeek');
                    foreach ([1, 2, 3, 4, 5, 6, 0] as $d) {
                        $sch = $schedules->get($d);
                        $data["day_{$d}_is_off"] = $sch ? (bool) $sch->isDayOff : ($d === 0);
                        $data["day_{$d}_start"] = $sch && $sch->startTime ? substr((string) $sch->startTime, 0, 5) : '09:00';
                        $data["day_{$d}_end"] = $sch && $sch->endTime ? substr((string) $sch->endTime, 0, 5) : '19:00';
                    }
                } else {
                    foreach ([1, 2, 3, 4, 5, 6, 0] as $d) {
                        $data["day_{$d}_is_off"] = ($d === 0);
                        $data["day_{$d}_start"] = '09:00';
                        $data["day_{$d}_end"] = '19:00';
                    }
                }

                return $data;
            })
            ->schema(function () use ($daysConfig): array {
                $schema = [
                    Select::make('barberId')
                        ->label('Employé')
                        ->relationship('barber', 'firstName', fn ($query) => $query->where('status', '!=', 'inactive'))
                        ->getOptionLabelFromRecordUsing(fn (Barber $record) => "{$record->firstName} {$record->lastName}")
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),
                ];

                $dayFields = [];
                foreach ($daysConfig as $d => $name) {
                    $dayFields[] = Section::make($name)
                        ->compact()
                        ->schema([
                            Checkbox::make("day_{$d}_is_off")
                                ->label('Jour de repos')
                                ->default($d === 0),
                            TimePicker::make("day_{$d}_start")
                                ->label('Début')
                                ->seconds(false)
                                ->default('09:00'),
                            TimePicker::make("day_{$d}_end")
                                ->label('Fin')
                                ->seconds(false)
                                ->default('19:00'),
                        ])
                        ->columns(3);
                }

                $schema[] = Grid::make(1)->schema($dayFields);

                return $schema;
            })
            ->action(function (array $data): void {
                $barberId = (int) $data['barberId'];

                foreach ([1, 2, 3, 4, 5, 6, 0] as $d) {
                    $isOff = (bool) ($data["day_{$d}_is_off"] ?? false);
                    $start = $data["day_{$d}_start"] ?? '09:00';
                    $end = $data["day_{$d}_end"] ?? '19:00';

                    StaffSchedule::updateOrCreate(
                        ['barberId' => $barberId, 'dayOfWeek' => $d],
                        [
                            'startTime' => $start ?: '09:00',
                            'endTime' => $end ?: '19:00',
                            'isDayOff' => $isOff,
                        ]
                    );
                }

                Notification::make()
                    ->title('Planning hebdomadaire enregistré avec succès !')
                    ->success()
                    ->send();
            });
    }
}
