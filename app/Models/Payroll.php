<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

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
}
