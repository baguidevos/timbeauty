<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'requiredVisits',
        'serviceId',
        'discountPercentage',
        'message',
        'cooldownDays',
        'validityDays',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'requiredVisits' => 'integer',
            'discountPercentage' => 'decimal:2',
            'cooldownDays' => 'integer',
            'validityDays' => 'integer',
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'serviceId');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
