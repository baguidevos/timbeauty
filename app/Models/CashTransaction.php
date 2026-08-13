<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashRegisterId',
        'type',
        'amount',
        'description',
        'referenceId',
        'createdBy',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:0',
        ];
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'cashRegisterId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }
}
