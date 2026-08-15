<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'barberId',
        'date',
        'clockIn',
        'clockOut',
        'status',
        'notes',
        'workedMinutes',
        'breakMinutes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clockIn' => 'datetime',
            'clockOut' => 'datetime',
            'workedMinutes' => 'integer',
            'breakMinutes' => 'integer',
        ];
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function isPresent(): bool
    {
        return $this->status === 'present';
    }

    public function isLate(): bool
    {
        return $this->status === 'late';
    }

    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }

    /**
     * Compute worked minutes from clock in and clock out times.
     */
    public function calculateWorkedMinutes(): int
    {
        if (! $this->clockIn) {
            return 0;
        }

        $end = $this->clockOut ? Carbon::parse($this->clockOut) : now();
        $start = Carbon::parse($this->clockIn);

        $diffMinutes = max(0, $start->diffInMinutes($end));
        $break = (int) ($this->breakMinutes ?? 0);

        return max(0, $diffMinutes - $break);
    }

    /**
     * Formatted string for worked hours (e.g. 7h30 or 45min).
     */
    public function getWorkedHoursFormattedAttribute(): string
    {
        $minutes = (int) ($this->workedMinutes ?? 0);
        if ($minutes <= 0 && $this->clockIn && ! $this->clockOut) {
            $minutes = $this->calculateWorkedMinutes();
        }

        if ($minutes <= 0) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $remMinutes = $minutes % 60;

        if ($hours === 0) {
            return "{$remMinutes} min";
        }

        if ($remMinutes === 0) {
            return "{$hours}h";
        }

        return sprintf('%dh%02d', $hours, $remMinutes);
    }

    /**
     * Formatted clock in time (HH:mm).
     */
    public function getClockInTimeAttribute(): ?string
    {
        return $this->clockIn ? Carbon::parse($this->clockIn)->format('H:i') : null;
    }

    /**
     * Formatted clock out time (HH:mm).
     */
    public function getClockOutTimeAttribute(): ?string
    {
        return $this->clockOut ? Carbon::parse($this->clockOut)->format('H:i') : null;
    }
}
