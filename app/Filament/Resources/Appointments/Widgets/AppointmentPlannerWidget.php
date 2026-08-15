<?php

namespace App\Filament\Resources\Appointments\Widgets;

use App\Models\Appointment;
use App\Models\Barber;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class AppointmentPlannerWidget extends Widget
{
    protected string $view = 'filament.resources.appointments.widgets.appointment-planner-widget';

    protected int|string|array $columnSpan = 'full';

    public string $viewMode = 'day';

    public string $currentDate;

    public ?string $selectedBarberId = null;

    public array $barbers = [];

    public array $appointments = [];

    public int $totalCount = 0;

    public int $todayCount = 0;

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

    public function loadBarbers(): void
    {
        $this->barbers = Barber::canPerformServices()
            ->select('id', 'firstName', 'lastName')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'name' => "{$b->firstName} {$b->lastName}",
            ])
            ->toArray();
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
        }
    }

    public function loadData(): void
    {
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
            ->whereBetween('date', [$startDate, $endDate]);

        if ($this->selectedBarberId) {
            $query->where('barberId', $this->selectedBarberId);
        }

        $appts = $query->get();
        $this->totalCount = $appts->count();

        // Stats counters
        $allAppts = Appointment::whereDate('date', $today)->get();
        $this->todayCount = $allAppts->count();
        $this->pendingCount = $allAppts->where('status', 'pending')->count();
        $this->inProgressCount = $allAppts->where('status', 'in_progress')->count();
        $this->completedCount = $allAppts->where('status', 'completed')->count();
        $this->cancelledCount = $allAppts->whereIn('status', ['cancelled', 'no_show'])->count();

        $this->appointments = $appts->map(function ($a) {
            return [
                'id' => $a->id,
                'client_name' => $a->client ? "{$a->client->firstName} {$a->client->lastName}" : 'Client anonyme',
                'client_phone' => $a->client?->phone ?? '',
                'barber_id' => $a->barberId,
                'barber_name' => $a->barber ? "{$a->barber->firstName} {$a->barber->lastName}" : 'N/A',
                'service_name' => $a->service?->name ?? 'Prestation',
                'service_price' => (float) ($a->service?->price ?? 0),
                'date' => Carbon::parse($a->date)->toDateString(),
                'start_time' => substr((string) $a->startTime, 0, 5),
                'end_time' => substr((string) $a->endTime, 0, 5),
                'status' => $a->status,
                'notes' => $a->notes,
            ];
        })->toArray();
    }
}
