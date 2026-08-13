<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'openingAmount',
        'closingAmount',
        'status',
        'openedAt',
        'closedAt',
        'openedBy',
    ];

    protected function casts(): array
    {
        return [
            'openingAmount' => 'decimal:0',
            'closingAmount' => 'decimal:0',
            'openedAt' => 'datetime',
            'closedAt' => 'datetime',
        ];
    }

    public function opener()
    {
        return $this->belongsTo(User::class, 'openedBy');
    }

    public function transactions()
    {
        return $this->hasMany(CashTransaction::class, 'cashRegisterId');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'cashRegisterId');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'cashRegisterId');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function close(float $closingAmount): void
    {
        $this->update([
            'closingAmount' => $closingAmount,
            'status' => 'closed',
            'closedAt' => now(),
        ]);
    }
}
