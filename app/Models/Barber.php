<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model implements Resourceable
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'firstName',
        'lastName',
        'phone',
        'address',
        'hireDate',
        'status',
        'jobTitle',
        'canPerformServices',
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
            'canPerformServices' => 'boolean',
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

    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make((string) $this->id)
            ->title($this->getFullName());
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isBarber(): bool
    {
        return $this->jobTitle === 'barber' || $this->canPerformServices;
    }

    public function scopeCanPerformServices($query)
    {
        return $query->where('canPerformServices', true)->where('status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getActivityDescription(string $action): string
    {
        $name = $this->getFullName();

        return match ($action) {
            'create' => "Nouvel employé/coiffeur ajouté : {$name} ({$this->jobTitle})",
            'update' => "Mise à jour du profil employé : {$name}",
            'delete' => "Suppression de l'employé : {$name}",
            default => "Action {$action} sur l'employé {$name}",
        };
    }
}
