<?php

namespace App\Filament\Resources\Appointments\Widgets;

use App\Models\Appointment;
use App\Models\Barber;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class AppointmentPlannerWidget extends Widget
{
    protected string $view = 'filament.resources.appointments.widgets.appointment-planner-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '5s';

    public string $viewMode = 'day';

    public string $currentDate = '';

    public ?string $selectedBarberId = null;

    public array $barbers = [];

    public array $appointments = [];

    public int $totalCount = 0;

    public int $todayCount = 0;

    public int $weekCount = 0;

    public int $pendingCount = 0;

    public int $inProgressCount = 0;

    public int $completedCount = 0;

    public int $cancelledCount = 0;

    public function mount(): void
    {
        $this->currentDate = Carbon::today()->toDateString();
        $this->loadBarbers();
        $this->loadData();
    }

    #[On('refreshAppointmentPlanner')]
    #[On('appointment-updated')]
    #[On('appointment-created')]
    #[On('appointment-deleted')]
    #[On('filament-tables::refresh')]
    #[On('close-modal')]
    public function refreshWidget(): void
    {
        $this->loadBarbers();
        $this->loadData();
    }

    public function updatedCurrentDate(): void
    {
        $this->loadData();
    }

    public function setDate(string $date): void
    {
        $this->currentDate = $date;
        $this->loadData();
    }

    public function loadBarbers(): void
    {
        $activeBarbers = Barber::canPerformServices()
            ->select('id', 'firstName', 'lastName')
            ->get()
            ->map(fn ($b) => [
                'id' => (string) $b->id,
                'name' => "{$b->firstName} {$b->lastName}",
            ])
            ->toArray();

        $this->barbers = $activeBarbers;
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->loadData();
    }

    public function filterBarber(?string $barberId): void
    {
        $this->selectedBarberId = $barberId ?: null;
        $this->loadData();
    }

    public function goToday(): void
    {
        $this->currentDate = Carbon::today()->toDateString();
        $this->loadData();
    }

    public function goPrev(): void
    {
        $date = Carbon::parse($this->currentDate);
        if ($this->viewMode === 'day') {
            $this->currentDate = $date->subDay()->toDateString();
        } elseif ($this->viewMode === 'week') {
            $this->currentDate = $date->subWeek()->toDateString();
        } else {
            $this->currentDate = $date->subMonth()->toDateString();
        }
        $this->loadData();
    }

    public function goNext(): void
    {
        $date = Carbon::parse($this->currentDate);
        if ($this->viewMode === 'day') {
            $this->currentDate = $date->addDay()->toDateString();
        } elseif ($this->viewMode === 'week') {
            $this->currentDate = $date->addWeek()->toDateString();
        } else {
            $this->currentDate = $date->addMonth()->toDateString();
        }
        $this->loadData();
    }

    public function updateStatus(int|string $appointmentId, string $newStatus): void
    {
        $appt = Appointment::find($appointmentId);
        if ($appt) {
            $appt->update(['status' => $newStatus]);
            Notification::make()
                ->title('Statut mis à jour !')
                ->success()
                ->send();

            $this->loadData();
            $this->dispatch('filament-tables::refresh');
            $this->dispatch('refreshAppointmentPlanner');
        }
    }

    public function loadData(): void
    {
        if (empty($this->currentDate)) {
            $this->currentDate = Carbon::today()->toDateString();
        }

        $current = Carbon::parse($this->currentDate);
        $today = Carbon::today()->toDateString();

        if ($this->viewMode === 'day') {
            $startDate = $current->toDateString();
            $endDate = $startDate;
        } elseif ($this->viewMode === 'week') {
            $startDate = $current->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
            $endDate = $current->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
        } else {
            $startDate = $current->copy()->startOfMonth()->toDateString();
            $endDate = $current->copy()->endOfMonth()->toDateString();
        }

        $query = Appointment::with(['client', 'barber', 'service'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if ($this->selectedBarberId) {
            $query->where('barberId', $this->selectedBarberId);
        }

        $appts = $query->orderBy('startTime')->get();
        $this->totalCount = $appts->count();

        // Week count relative to current date
        $weekStart = $current->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd = $current->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
        $this->weekCount = Appointment::whereDate('date', '>=', $weekStart)
            ->whereDate('date', '<=', $weekEnd)
            ->count();

        // Stats counters for the current period
        $this->todayCount = Appointment::whereDate('date', $today)->count();
        $this->pendingCount = $appts->where('status', 'pending')->count();
        $this->inProgressCount = $appts->where('status', 'in_progress')->count();
        $this->completedCount = $appts->where('status', 'completed')->count();
        $this->cancelledCount = $appts->whereIn('status', ['cancelled', 'no_show'])->count();

        // Ensure any barber who has appointments in the loaded list is also present in $this->barbers
        $existingBarberIds = array_column($this->barbers, 'id');
        foreach ($appts as $appt) {
            if ($appt->barber && ! in_array((string) $appt->barber->id, $existingBarberIds, true)) {
                $this->barbers[] = [
                    'id' => (string) $appt->barber->id,
                    'name' => "{$appt->barber->firstName} {$appt->barber->lastName}",
                ];
                $existingBarberIds[] = (string) $appt->barber->id;
            }
        }

        $this->appointments = $appts->map(function ($a) {
            $parsedDate = $a->date instanceof Carbon ? $a->date->toDateString() : Carbon::parse($a->date)->toDateString();
            $startTime = substr((string) $a->startTime, 0, 5);
            $endTime = substr((string) $a->endTime, 0, 5);

            if (preg_match('/^(\d):(\d{2})$/', $startTime)) {
                $startTime = '0'.$startTime;
            }
            if (preg_match('/^(\d):(\d{2})$/', $endTime)) {
                $endTime = '0'.$endTime;
            }

            return [
                'id' => $a->id,
                'client_name' => $a->client ? "{$a->client->firstName} {$a->client->lastName}" : 'Client anonyme',
                'client_phone' => $a->client?->phone ?? '',
                'barber_id' => (string) $a->barberId,
                'barber_name' => $a->barber ? "{$a->barber->firstName} {$a->barber->lastName}" : 'Personnel non assigné',
                'service_name' => $a->service?->name ?? 'Prestation',
                'service_price' => (float) ($a->service?->price ?? 0),
                'date' => $parsedDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $a->status,
                'notes' => $a->notes,
            ];
        })->toArray();
    }
}
