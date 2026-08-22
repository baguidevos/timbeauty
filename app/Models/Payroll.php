<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'barberId',
        'month',
        'year',
        'fixedSalary',
        'commissions',
        'bonus',
        'advances',
        'deductions',
        'netSalary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'fixedSalary' => 'decimal:0',
            'commissions' => 'decimal:0',
            'bonus' => 'decimal:0',
            'advances' => 'decimal:0',
            'deductions' => 'decimal:0',
            'netSalary' => 'decimal:0',
        ];
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class, 'payrollId');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === 'partially_paid';
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->salaryPayments->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->netSalary - $this->total_paid);
    }

    public function getActivityDescription(string $action): string
    {
        $barberName = $this->barber ? $this->barber->getFullName() : "Employé #{$this->barberId}";
        $net = number_format((float) $this->netSalary, 0, ',', ' ');
        $period = sprintf('%02d/%d', $this->month, $this->year);

        return match ($action) {
            'create' => "Fiche de paie générée pour {$barberName} ({$period}) - Net : {$net} FCFA",
            'update' => "Mise à jour de la fiche de paie #{$this->id} ({$barberName}) - Statut : {$this->status}",
            'delete' => "Suppression de la fiche de paie #{$this->id} ({$barberName})",
            default => "Action {$action} sur la fiche de paie #{$this->id}",
        };
    }
}
