<?php

namespace App\Models;

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
}
