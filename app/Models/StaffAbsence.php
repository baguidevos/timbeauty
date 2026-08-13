<?php

namespace App\Models;

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
}
