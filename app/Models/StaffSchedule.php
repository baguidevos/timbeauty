<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'barberId',
        'dayOfWeek',
        'startTime',
        'endTime',
        'isDayOff',
    ];

    protected function casts(): array
    {
        return [
            'dayOfWeek' => 'integer',
            'isDayOff' => 'boolean',
        ];
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function getDayNameAttribute(): string
    {
        $days = [
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
        ];

        return $days[$this->dayOfWeek] ?? "Jour {$this->dayOfWeek}";
    }

    public function getFormattedHoursAttribute(): string
    {
        if ($this->isDayOff) {
            return 'Repos';
        }

        if (! $this->startTime || ! $this->endTime) {
            return 'Non défini';
        }

        $start = substr((string) $this->startTime, 0, 5);
        $end = substr((string) $this->endTime, 0, 5);

        return "{$start} - {$end}";
    }
}
