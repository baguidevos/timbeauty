<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'minPoints',
        'pointsPerFCFA',
        'discountPercentage',
        'color',
        'icon',
        'perks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'minPoints' => 'integer',
            'pointsPerFCFA' => 'decimal:4',
            'discountPercentage' => 'decimal:2',
        ];
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'loyaltyTierId');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
