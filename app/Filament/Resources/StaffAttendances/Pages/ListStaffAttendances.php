<?php

namespace App\Filament\Resources\StaffAttendances\Pages;

use App\Filament\Resources\StaffAttendances\StaffAttendanceResource;
use App\Models\Barber;
use App\Models\Setting;
use App\Models\StaffAttendance;
use App\Models\StaffSchedule;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class ListStaffAttendances extends ListRecords
{
    protected static string $resource = StaffAttendanceResource::class;

    protected string $view = 'filament.resources.staff-attendances.pages.list-staff-attendances';

    public ?string $activeTab = 'today';

    public string $currentDate;

    public string $historyFrom;

    public string $historyTo;

    public string $historyBarberId = 'all';

    public string $historyStatus = 'all';

    public function mount(): void
    {
        $today = Carbon::today()->toDateString();
        $this->currentDate = $today;
        $this->historyTo = $today;
        $this->historyFrom = Carbon::today()->subDays(29)->toDateString(); // Default 30 days
    }

    public function getTitle(): string|Htmlable
    {
        return 'Pointage & Présences';
    }

    public function getHeading(): Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Pointage & Présences</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Pointage en direct des arrivées, départs et suivi des heures travaillées</span>
                </div>
            </div>
        ');
    }

    public function applyPreset(int $days): void
    {
        $this->historyTo = Carbon::today()->toDateString();
        $this->historyFrom = Carbon::today()->subDays($days - 1)->toDateString();
    }

    public function isPresetActive(int $days): bool
    {
        $expectedFrom = Carbon::today()->subDays($days - 1)->toDateString();
        $today = Carbon::today()->toDateString();

        return $this->historyTo === $today && $this->historyFrom === $expectedFrom;
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

    public function getTodayDataProperty(): array
    {
        $today = Carbon::today()->toDateString();
        $barbers = Barber::where('status', '!=', 'inactive')->with(['schedules'])->get();
        $totalStaff = $barbers->count();

        $attendances = StaffAttendance::whereDate('date', $today)
            ->get()
            ->keyBy('barberId');

        $presentCount = 0;
        $lateCount = 0;
        $absentCount = 0;
        $scheduledCount = 0;

        $records = [];
        foreach ($barbers as $barber) {
            $att = $attendances->get($barber->id);

            $jobLabel = match ($barber->jobTitle) {
                'barber' => 'Coiffeur',
                'manager' => 'Gérant',
                'receptionist' => 'Réceptionniste',
                'cashier' => 'Caissier',
                'cleaner' => 'Entretien',
                default => 'Personnel',
            };

            $status = $att ? $att->status : 'scheduled';
            if ($status === 'present') {
                $presentCount++;
            } elseif ($status === 'late') {
                $lateCount++;
            } elseif ($status === 'absent') {
                $absentCount++;
            } else {
                $scheduledCount++;
            }

            $clockInStr = $att && $att->clockIn ? Carbon::parse($att->clockIn)->format('H:i') : null;
            $clockOutStr = $att && $att->clockOut ? Carbon::parse($att->clockOut)->format('H:i') : null;

            $records[] = [
                'barber_id' => $barber->id,
                'barber_name' => "{$barber->firstName} {$barber->lastName}",
                'barber_status' => $barber->status,
                'job_title' => $jobLabel,
                'photo' => $barber->photo,
                'attendance_id' => $att?->id,
                'status' => $status,
                'clock_in' => $clockInStr,
                'clock_out' => $clockOutStr,
                'notes' => $att?->notes ?? '',
                'worked_hours' => $att ? $att->worked_hours_formatted : '0 min',
                'is_clocked_in' => (bool) ($att && $att->clockIn && ! $att->clockOut),
                'is_clocked_out' => (bool) ($att && $att->clockIn && $att->clockOut),
            ];
        }

        $attended = $presentCount + $lateCount;
        $attendanceRate = $totalStaff > 0 ? (int) round(($attended / $totalStaff) * 100) : 0;

        return [
            'stats' => [
                'present' => $presentCount,
                'late' => $lateCount,
                'absent' => $absentCount,
                'scheduled' => $scheduledCount,
                'total' => $totalStaff,
                'attendanceRate' => $attendanceRate,
            ],
            'records' => $records,
        ];
    }

    public function clockIn(int $barberId): void
    {
        $today = Carbon::today()->toDateString();
        $barber = Barber::findOrFail($barberId);

        $now = now();
        $todayDayOfWeek = Carbon::today()->dayOfWeek;

        $schedule = StaffSchedule::where('barberId', $barberId)
            ->where('dayOfWeek', $todayDayOfWeek)
            ->first();

        if ($schedule && ! $schedule->isDayOff && $schedule->startTime) {
            $expectedStartTime = substr((string) $schedule->startTime, 0, 5);
        } else {
            $expectedStartTime = (string) Setting::get('default_opening_time', Setting::get('business_hours_start', '08:00'));
        }

        $isLate = $now->format('H:i') > $expectedStartTime;

        StaffAttendance::updateOrCreate(
            ['barberId' => $barberId, 'date' => $today],
            [
                'clockIn' => $now,
                'status' => $isLate ? 'late' : 'present',
            ]
        );

        Notification::make()
            ->title("Arrivée pointée pour {$barber->firstName} {$barber->lastName}")
            ->body('Pointage enregistré à '.$now->format('H:i').($isLate ? ' (En retard)' : ' (À l\'heure)'))
            ->success()
            ->send();
    }

    public function clockOut(int $barberId): void
    {
        $today = Carbon::today()->toDateString();
        $attendance = StaffAttendance::where('barberId', $barberId)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance) {
            return;
        }

        $now = now();
        $attendance->clockOut = $now;
        $attendance->workedMinutes = $attendance->calculateWorkedMinutes();
        $attendance->save();

        $barber = Barber::find($barberId);
        $barberName = $barber ? "{$barber->firstName} {$barber->lastName}" : 'L\'employé';

        Notification::make()
            ->title("Départ pointé pour {$barberName}")
            ->body('Départ enregistré à '.$now->format('H:i')." • Total travaillé : {$attendance->worked_hours_formatted}")
            ->success()
            ->send();
    }

    public function markAbsent(int $barberId): void
    {
        $today = Carbon::today()->toDateString();
        $barber = Barber::findOrFail($barberId);

        StaffAttendance::updateOrCreate(
            ['barberId' => $barberId, 'date' => $today],
            [
                'status' => 'absent',
                'clockIn' => null,
                'clockOut' => null,
                'workedMinutes' => 0,
            ]
        );

        Notification::make()
            ->title("{$barber->firstName} {$barber->lastName} marqué absent")
            ->warning()
            ->send();
    }

    public function setStatus(int $barberId, string $status): void
    {
        $today = Carbon::today()->toDateString();
        $barber = Barber::findOrFail($barberId);

        StaffAttendance::updateOrCreate(
            ['barberId' => $barberId, 'date' => $today],
            [
                'status' => $status,
            ]
        );

        Notification::make()
            ->title("Statut de {$barber->firstName} mis à jour : ".match ($status) {
                'present' => 'Présent',
                'late' => 'En retard',
                'absent' => 'Absent',
                'half_day' => 'Demi-journée',
                default => 'Programmé',
            })
            ->success()
            ->send();
    }

    public function updateNotes(int $barberId, string $notes): void
    {
        $today = Carbon::today()->toDateString();

        StaffAttendance::updateOrCreate(
            ['barberId' => $barberId, 'date' => $today],
            ['notes' => $notes]
        );

        Notification::make()
            ->title('Notes enregistrées')
            ->success()
            ->send();
    }

    public function getHistoryRecordsProperty(): Collection
    {
        $query = StaffAttendance::with('barber')
            ->whereBetween('date', [$this->historyFrom, $this->historyTo])
            ->orderBy('date', 'desc')
            ->orderBy('clockIn', 'desc');

        if ($this->historyBarberId !== 'all') {
            $query->where('barberId', $this->historyBarberId);
        }

        if ($this->historyStatus !== 'all') {
            $query->where('status', $this->historyStatus);
        }

        return $query->get();
    }

    public function getHistorySummariesProperty(): array
    {
        $barbers = Barber::where('status', '!=', 'inactive')->get();
        $attendances = StaffAttendance::whereBetween('date', [$this->historyFrom, $this->historyTo])->get();

        $fromDate = Carbon::parse($this->historyFrom);
        $toDate = Carbon::parse($this->historyTo);
        $totalDays = max(1, $fromDate->diffInDays($toDate) + 1);

        $summaries = [];
        foreach ($barbers as $barber) {
            $staffAtt = $attendances->where('barberId', $barber->id);

            $daysPresent = $staffAtt->where('status', 'present')->count();
            $daysLate = $staffAtt->where('status', 'late')->count();
            $daysAbsent = $staffAtt->where('status', 'absent')->count();
            $daysHalfDay = $staffAtt->where('status', 'half_day')->count();

            $totalWorkedMinutes = (int) $staffAtt->sum('workedMinutes');
            $hours = intdiv($totalWorkedMinutes, 60);
            $remMin = $totalWorkedMinutes % 60;
            $workedHoursFormatted = sprintf('%dh%02d', $hours, $remMin);

            $attendedDays = $daysPresent + $daysLate + ($daysHalfDay * 0.5);
            $rate = $totalDays > 0 ? min(100, (int) round(($attendedDays / $totalDays) * 100)) : 0;

            $jobLabel = match ($barber->jobTitle) {
                'barber' => 'Coiffeur',
                'manager' => 'Gérant',
                'receptionist' => 'Réceptionniste',
                'cashier' => 'Caissier',
                'cleaner' => 'Entretien',
                default => 'Personnel',
            };

            $summaries[] = [
                'id' => $barber->id,
                'name' => "{$barber->firstName} {$barber->lastName}",
                'job_title' => $jobLabel,
                'days_present' => $daysPresent,
                'days_late' => $daysLate,
                'days_absent' => $daysAbsent,
                'days_half_day' => $daysHalfDay,
                'total_worked_minutes' => $totalWorkedMinutes,
                'total_hours_formatted' => $workedHoursFormatted,
                'attendance_rate' => $rate,
            ];
        }

        return $summaries;
    }

    public function getHistoryTotalsProperty(): array
    {
        $records = StaffAttendance::whereBetween('date', [$this->historyFrom, $this->historyTo])->get();

        $totalMinutes = (int) $records->sum('workedMinutes');
        $hours = intdiv($totalMinutes, 60);
        $remMin = $totalMinutes % 60;

        $presentDays = $records->where('status', 'present')->count();
        $lateDays = $records->where('status', 'late')->count();
        $absentDays = $records->where('status', 'absent')->count();

        $summaries = $this->historySummaries;
        $avgRate = count($summaries) > 0 ? (int) round(collect($summaries)->avg('attendance_rate')) : 0;

        return [
            'total_hours_formatted' => sprintf('%dh%02d', $hours, $remMin),
            'avg_attendance_rate' => $avgRate,
            'total_present_days' => $presentDays,
            'total_late_days' => $lateDays,
            'total_absent_days' => $absentDays,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->createAttendanceAction(),
        ];
    }

    public function createAttendanceAction(): Action
    {
        return Action::make('createAttendance')
            ->label('Enregistrer une présence')
            ->icon('heroicon-o-plus')
            ->color('warning')
            ->modalHeading('Enregistrer un pointage / présence')
            ->modalWidth(Width::Large)
            ->schema([
                Select::make('barberId')
                    ->label('Membre du personnel')
                    ->relationship('barber', 'firstName')
                    ->getOptionLabelFromRecordUsing(fn (Barber $record) => "{$record->firstName} {$record->lastName}")
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->required()
                    ->native(false),

                DateTimePicker::make('clockIn')
                    ->label('Heure d\'arrivée')
                    ->native(false)
                    ->seconds(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => $this->autoCalcWorkedMinutes($get, $set)),

                DateTimePicker::make('clockOut')
                    ->label('Heure de départ')
                    ->native(false)
                    ->seconds(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => $this->autoCalcWorkedMinutes($get, $set)),

                TextInput::make('breakMinutes')
                    ->label('Pause (minutes)')
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => $this->autoCalcWorkedMinutes($get, $set)),

                TextInput::make('workedMinutes')
                    ->label('Minutes travaillées')
                    ->numeric()
                    ->default(0),

                ToggleButtons::make('status')
                    ->label('Statut')
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
                    ->default('present')
                    ->inline()
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                StaffAttendance::create($data);

                Notification::make()
                    ->title('Présence enregistrée avec succès')
                    ->success()
                    ->send();
            });
    }

    public function editAttendanceAction(): Action
    {
        return Action::make('editAttendance')
            ->icon('heroicon-o-pencil-square')
            ->iconButton()
            ->color('gray')
            ->tooltip('Modifier la présence')
            ->modalHeading('Modifier le pointage')
            ->modalWidth(Width::Large)
            ->fillForm(function (array $arguments): array {
                $record = StaffAttendance::findOrFail($arguments['record']);

                return [
                    'barberId' => $record->barberId,
                    'date' => $record->date,
                    'clockIn' => $record->clockIn,
                    'clockOut' => $record->clockOut,
                    'breakMinutes' => $record->breakMinutes,
                    'workedMinutes' => $record->workedMinutes,
                    'status' => $record->status,
                    'notes' => $record->notes,
                ];
            })
            ->schema([
                Select::make('barberId')
                    ->label('Membre du personnel')
                    ->relationship('barber', 'firstName')
                    ->getOptionLabelFromRecordUsing(fn (Barber $record) => "{$record->firstName} {$record->lastName}")
                    ->required(),

                DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->native(false),

                DateTimePicker::make('clockIn')
                    ->label('Heure d\'arrivée')
                    ->native(false)
                    ->seconds(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => $this->autoCalcWorkedMinutes($get, $set)),

                DateTimePicker::make('clockOut')
                    ->label('Heure de départ')
                    ->native(false)
                    ->seconds(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => $this->autoCalcWorkedMinutes($get, $set)),

                TextInput::make('breakMinutes')
                    ->label('Pause (minutes)')
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => $this->autoCalcWorkedMinutes($get, $set)),

                TextInput::make('workedMinutes')
                    ->label('Minutes travaillées')
                    ->numeric()
                    ->default(0),

                ToggleButtons::make('status')
                    ->label('Statut')
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
                    ->inline()
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ])
            ->action(function (array $arguments, array $data): void {
                $record = StaffAttendance::findOrFail($arguments['record']);
                $record->update($data);

                Notification::make()
                    ->title('Présence mise à jour')
                    ->success()
                    ->send();
            });
    }

    public function deleteAttendanceAction(): Action
    {
        return Action::make('deleteAttendance')
            ->icon('heroicon-o-trash')
            ->iconButton()
            ->color('danger')
            ->tooltip('Supprimer')
            ->requiresConfirmation()
            ->modalHeading('Supprimer ce pointage ?')
            ->modalDescription('Êtes-vous sûr de vouloir supprimer cet enregistrement de présence ?')
            ->action(function (array $arguments): void {
                $record = StaffAttendance::findOrFail($arguments['record']);
                $record->delete();

                Notification::make()
                    ->title('Enregistrement supprimé')
                    ->success()
                    ->send();
            });
    }

    protected function autoCalcWorkedMinutes(Get $get, Set $set): void
    {
        $clockIn = $get('clockIn');
        $clockOut = $get('clockOut');
        $break = (int) ($get('breakMinutes') ?? 0);

        if (! $clockIn || ! $clockOut) {
            return;
        }

        try {
            $start = Carbon::parse($clockIn);
            $end = Carbon::parse($clockOut);
            $diff = max(0, $start->diffInMinutes($end));
            $set('workedMinutes', max(0, $diff - $break));
        } catch (\Throwable) {
            // Ignore
        }
    }
}
