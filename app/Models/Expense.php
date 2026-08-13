<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoryId',
        'amount',
        'date',
        'description',
        'beneficiary',
        'paymentMethod',
        'receipt',
        'createdBy',
        'cashRegisterId',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:0',
            'date' => 'date',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'categoryId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'cashRegisterId');
    }
}
