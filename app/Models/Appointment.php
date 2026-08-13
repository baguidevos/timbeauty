<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
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
