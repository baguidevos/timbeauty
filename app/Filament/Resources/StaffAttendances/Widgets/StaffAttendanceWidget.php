<?php

namespace App\Filament\Resources\StaffAttendances\Widgets;

use App\Models\Barber;
use App\Models\StaffAttendance;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class StaffAttendanceWidget extends Widget
{
    protected string $view = 'filament.resources.staff-attendances.widgets.staff-attendance-widget';

    protected int|string|array $columnSpan = 'full';

    public string $currentDate;

    public array $staffRecords = [];

    public int $totalStaff = 0;

    public int $presentCount = 0;

    public int $lateCount = 0;

    public int $absentCount = 0;

    public int $scheduledCount = 0;

    public int $attendanceRate = 0;

    public function mount(): void
    {
        $this->currentDate = Carbon::today()->toDateString();
        $this->loadData();
    }

    public function loadData(): void
    {
        $today = Carbon::today()->toDateString();
        $barbers = Barber::where('status', '!=', 'inactive')->get();
        $this->totalStaff = $barbers->count();

        $attendances = StaffAttendance::whereDate('date', $today)
            ->get()
            ->keyBy('barberId');

        $this->presentCount = 0;
        $this->lateCount = 0;
        $this->absentCount = 0;
        $this->scheduledCount = 0;

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
                $this->presentCount++;
            } elseif ($status === 'late') {
                $this->lateCount++;
            } elseif ($status === 'absent') {
                $this->absentCount++;
            } else {
                $this->scheduledCount++;
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

        $this->staffRecords = $records;

        $attended = $this->presentCount + $this->lateCount;
        $this->attendanceRate = $this->totalStaff > 0
            ? (int) round(($attended / $this->totalStaff) * 100)
            : 0;
    }

    public function clockIn(int $barberId): void
    {
        $today = Carbon::today()->toDateString();
        $barber = Barber::findOrFail($barberId);

        $now = now();
        $isLate = $now->format('H:i') > '09:00';

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

        $this->loadData();
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

        $this->loadData();
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

        $this->loadData();
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
            ->title("Statut de {$barber->firstName} mis à jour")
            ->success()
            ->send();

        $this->loadData();
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

        $this->loadData();
    }

    public function setBarberStatus(int $barberId, string $status): void
    {
        $barber = Barber::findOrFail($barberId);
        $barber->status = $status;
        $barber->save();

        $label = match ($status) {
            'active' => 'Actif',
            'on_leave' => 'En congé',
            'inactive' => 'Inactif',
            default => $status,
        };

        Notification::make()
            ->title("Statut RH de {$barber->firstName} {$barber->lastName} : {$label}")
            ->success()
            ->send();

        $this->loadData();
    }
}
