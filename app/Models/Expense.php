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

    protected static function booted(): void
    {
        static::creating(function (Expense $expense): void {
            if (! $expense->cashRegisterId) {
                $openRegister = CashRegister::where('status', 'open')->first();
                if ($openRegister) {
                    $expense->cashRegisterId = $openRegister->id;
                }
            }
        });

        static::created(function (Expense $expense): void {
            if ($expense->paymentMethod === 'cash' && $expense->cashRegisterId) {
                CashTransaction::create([
                    'cashRegisterId' => $expense->cashRegisterId,
                    'type' => 'expense',
                    'amount' => $expense->amount,
                    'description' => 'Dépense : '.($expense->category?->name ?? 'Générale').($expense->beneficiary ? ' — '.$expense->beneficiary : ''),
                    'referenceId' => (string) $expense->id,
                    'createdBy' => $expense->createdBy ?? auth()->id(),
                ]);
            }
        });
    }
}
