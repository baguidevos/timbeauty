<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payrollId',
        'barberId',
        'amount',
        'date',
        'method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:0',
            'date' => 'date',
        ];
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payrollId');
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }
}
