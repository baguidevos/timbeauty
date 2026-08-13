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
}
