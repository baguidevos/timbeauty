<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'barberId',
        'month',
        'year',
        'targetAmount',
        'createdBy',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'targetAmount' => 'decimal:0',
        ];
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function isShopTarget(): bool
    {
        return $this->type === 'shop';
    }

    public function isBarberTarget(): bool
    {
        return $this->type === 'barber';
    }
}
