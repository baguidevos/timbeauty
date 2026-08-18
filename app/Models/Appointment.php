<?php

namespace App\Models;

use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Appointment extends Model implements Eventable
{
    use HasFactory;

    protected $fillable = [
        'clientId',
        'barberId',
        'serviceId',
        'date',
        'startTime',
        'endTime',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function toCalendarEvent(): CalendarEvent
    {
        $dateStr = $this->date instanceof Carbon ? $this->date->toDateString() : Carbon::parse($this->date)->toDateString();
        $startStr = substr((string) $this->startTime, 0, 5);
        $endStr = substr((string) $this->endTime, 0, 5);

        if (preg_match('/^(\d):(\d{2})$/', $startStr)) {
            $startStr = '0'.$startStr;
        }
        if (preg_match('/^(\d):(\d{2})$/', $endStr)) {
            $endStr = '0'.$endStr;
        }

        $startDate = Carbon::parse("{$dateStr} {$startStr}");
        $endDate = Carbon::parse("{$dateStr} {$endStr}");

        $clientName = $this->client ? "{$this->client->firstName} {$this->client->lastName}" : 'Client anonyme';
        $serviceName = $this->service?->name ?? 'Prestation';
        $barberName = $this->barber ? "{$this->barber->firstName} {$this->barber->lastName}" : 'Personnel non assigné';

        $color = match ($this->status) {
            'confirmed' => '#0284c7',
            'in_progress' => '#059669',
            'completed' => '#047857',
            'cancelled', 'no_show' => '#e11d48',
            default => '#d97706',
        };

        return CalendarEvent::make($this)
            ->title("{$clientName} • {$serviceName}")
            ->start($startDate)
            ->end($endDate)
            ->resourceId((string) $this->barberId)
            ->backgroundColor($color)
            ->extendedProps([
                'id' => $this->id,
                'status' => $this->status,
                'statusLabel' => match ($this->status) {
                    'confirmed' => 'Confirmé',
                    'in_progress' => 'En cours',
                    'completed' => 'Terminé',
                    'cancelled' => 'Annulé',
                    'no_show' => 'Absent',
                    default => 'En attente',
                },
                'clientName' => $clientName,
                'clientPhone' => $this->client?->phone ?? '',
                'serviceName' => $serviceName,
                'barberName' => $barberName,
                'formattedTime' => "{$startStr} - {$endStr}",
                'price' => $this->service ? number_format($this->service->price, 0, ',', ' ').' FCFA' : '',
            ]);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'serviceId');
    }

    public function photos()
    {
        return $this->hasMany(AppointmentPhoto::class, 'appointmentId');
    }
}
