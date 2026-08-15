<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAbsence extends Model
{
    use HasFactory;

    protected $fillable = [
        'barberId',
        'startDate',
        'endDate',
        'reason',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'startDate' => 'date',
            'endDate' => 'date',
        ];
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function getDaysCountAttribute(): int
    {
        if (! $this->startDate || ! $this->endDate) {
            return 1;
        }

        return (int) max(1, Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'vacation', 'leave' => 'Congé payé',
            'sick', 'sick_leave' => 'Maladie',
            'personal' => 'Personnel',
            'absence' => 'Absence injustifiée',
            default => 'Autre',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'vacation', 'leave' => 'info',
            'sick', 'sick_leave' => 'warning',
            'personal' => 'primary',
            'absence' => 'danger',
            default => 'gray',
        };
    }

    public function isCurrentlyActive(): bool
    {
        $today = Carbon::today();

        return $today->gte(Carbon::parse($this->startDate)) && $today->lte(Carbon::parse($this->endDate));
    }
}
