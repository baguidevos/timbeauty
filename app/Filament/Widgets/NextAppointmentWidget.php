<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class NextAppointmentWidget extends Widget
{
    protected string $view = 'filament.widgets.next-appointment-widget';

    protected int|string|array $columnSpan = 'full';

    public ?array $appointment = null;

    public string $countdownText = 'Créneau libre';

    public bool $isSoon = false;

    public bool $isPast = false;

    public function mount(): void
    {
        $this->loadNextAppointment();
    }

    public function loadNextAppointment(): void
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $nextAppt = Appointment::with(['client', 'barber', 'service'])
            ->whereDate('date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('startTime', 'asc')
            ->get()
            ->first(function ($appt) use ($now, $today) {
                $apptDateTime = Carbon::parse("{$today} {$appt->startTime}");

                return $apptDateTime->timestamp >= ($now->timestamp - 3600);
            });

        if ($nextAppt) {
            $this->appointment = [
                'id' => $nextAppt->id,
                'client_name' => $nextAppt->client ? "{$nextAppt->client->firstName} {$nextAppt->client->lastName}" : 'Client anonyme',
                'barber_name' => $nextAppt->barber ? "{$nextAppt->barber->firstName} {$nextAppt->barber->lastName}" : 'N/A',
                'service_name' => $nextAppt->service?->name ?? 'Prestation',
                'start_time' => $nextAppt->startTime,
                'end_time' => $nextAppt->endTime,
                'status' => $nextAppt->status,
            ];

            $apptDateTime = Carbon::parse("{$today} {$nextAppt->startTime}");
            $diffMinutes = (int) $now->diffInMinutes($apptDateTime, false);

            if ($diffMinutes < 0) {
                $absMins = abs($diffMinutes);
                $hrs = floor($absMins / 60);
                $remMins = $absMins % 60;
                $this->countdownText = $hrs > 0 ? "Il y a {$hrs}h".sprintf('%02d', $remMins) : "Il y a {$absMins} min";
                $this->isPast = true;
                $this->isSoon = false;
            } elseif ($diffMinutes < 1) {
                $this->countdownText = 'Maintenant';
                $this->isPast = false;
                $this->isSoon = true;
            } elseif ($diffMinutes < 15) {
                $this->countdownText = "Dans {$diffMinutes} min";
                $this->isPast = false;
                $this->isSoon = true;
            } else {
                $hrs = floor($diffMinutes / 60);
                $remMins = $diffMinutes % 60;
                $this->countdownText = $hrs > 0 ? "Dans {$hrs}h".sprintf('%02d', $remMins) : "Dans {$diffMinutes} min";
                $this->isPast = false;
                $this->isSoon = false;
            }
        } else {
            $this->appointment = null;
        }
    }
}
