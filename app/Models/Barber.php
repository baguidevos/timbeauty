<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstName',
        'lastName',
        'phone',
        'address',
        'hireDate',
        'status',
        'specialties',
        'photo',
        'remunerationType',
        'fixedSalary',
        'commissionRate',
        'perServiceRate',
        'userId',
    ];

    protected function casts(): array
    {
        return [
            'hireDate' => 'date',
            'fixedSalary' => 'decimal:0',
            'commissionRate' => 'decimal:2',
            'perServiceRate' => 'decimal:0',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'barberId');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'barberId');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'barberId');
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class, 'barberId');
    }

    public function schedules()
    {
        return $this->hasMany(StaffSchedule::class, 'barberId');
    }

    public function absences()
    {
        return $this->hasMany(StaffAbsence::class, 'barberId');
    }

    public function attendances()
    {
        return $this->hasMany(StaffAttendance::class, 'barberId');
    }

    public function appointmentPhotos()
    {
        return $this->hasMany(AppointmentPhoto::class, 'barberId');
    }

    public function revenueTargets()
    {
        return $this->hasMany(RevenueTarget::class, 'barberId');
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
