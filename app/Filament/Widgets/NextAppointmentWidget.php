<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatHelper;
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

        // 1. Chercher les rendez-vous actifs d'aujourd'hui
        $todayAppts = Appointment::with(['client', 'barber', 'service'])
            ->whereDate('date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->orderBy('startTime', 'asc')
            ->get();

        $selectedAppt = null;

        // Priorité A : Rendez-vous "En cours"
        $inProgress = $todayAppts->firstWhere('status', 'in_progress');
        if ($inProgress) {
            $selectedAppt = $inProgress;
        } else {
            // Priorité B : Prochain rendez-vous aujourd'hui (dans le futur ou récent < 30min)
            $upcomingToday = $todayAppts->first(function ($appt) use ($now, $today) {
                $apptDateTime = Carbon::parse("{$today} {$appt->startTime}");

                return $apptDateTime->timestamp >= ($now->timestamp - 1800);
            });

            if ($upcomingToday) {
                $selectedAppt = $upcomingToday;
            } elseif ($todayAppts->isNotEmpty()) {
                // Priorité C : Rendez-vous d'aujourd'hui en attente non encore traité (ex: 09:00 en attente)
                $selectedAppt = $todayAppts->last();
            }
        }

        // Priorité D : Si aucun RDV actif aujourd'hui, chercher le prochain RDV des jours à venir
        if (! $selectedAppt) {
            $selectedAppt = Appointment::with(['client', 'barber', 'service'])
                ->where('date', '>', $today)
                ->whereIn('status', ['pending', 'confirmed'])
                ->orderBy('date', 'asc')
                ->orderBy('startTime', 'asc')
                ->first();
        }

        if ($selectedAppt) {
            $apptDate = $selectedAppt->date instanceof Carbon
                ? $selectedAppt->date->toDateString()
                : Carbon::parse($selectedAppt->date)->toDateString();

            $isToday = ($apptDate === $today);
            $isTomorrow = ($apptDate === Carbon::tomorrow()->toDateString());
            $timeFormatted = FormatHelper::formatTime($selectedAppt->startTime);

            $this->appointment = [
                'id' => $selectedAppt->id,
                'client_name' => $selectedAppt->client ? $selectedAppt->client->fullName : 'Client anonyme',
                'barber_name' => $selectedAppt->barber ? $selectedAppt->barber->fullName : 'N/A',
                'service_name' => $selectedAppt->service?->name ?? 'Prestation',
                'start_time' => $selectedAppt->startTime,
                'end_time' => $selectedAppt->endTime,
                'status' => $selectedAppt->status,
                'date_badge' => $isToday ? 'Aujourd\'hui' : ($isTomorrow ? 'Demain' : Carbon::parse($apptDate)->format('d/m')),
                'formatted_time' => $timeFormatted,
            ];

            if ($selectedAppt->status === 'in_progress') {
                $this->countdownText = 'En cours';
                $this->isPast = false;
                $this->isSoon = true;
            } elseif ($isToday) {
                $apptDateTime = Carbon::parse("{$today} {$selectedAppt->startTime}");
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
                } elseif ($diffMinutes < 30) {
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
            } elseif ($isTomorrow) {
                $this->countdownText = 'Demain';
                $this->isPast = false;
                $this->isSoon = false;
            } else {
                $days = (int) $now->diffInDays(Carbon::parse($apptDate));
                $this->countdownText = "Dans {$days} j";
                $this->isPast = false;
                $this->isSoon = false;
            }
        } else {
            $this->appointment = null;
        }
    }
}
