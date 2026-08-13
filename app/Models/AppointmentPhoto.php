<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointmentId',
        'clientId',
        'barberId',
        'type',
        'url',
        'caption',
        'tags',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointmentId');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function isBefore(): bool
    {
        return $this->type === 'before';
    }

    public function isAfter(): bool
    {
        return $this->type === 'after';
    }
}
