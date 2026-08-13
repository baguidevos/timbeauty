<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'value',
        'startDate',
        'endDate',
        'minVisits',
        'forLoyalOnly',
        'maxUsages',
        'currentUsages',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:0',
            'startDate' => 'date',
            'endDate' => 'date',
            'minVisits' => 'integer',
            'forLoyalOnly' => 'boolean',
            'maxUsages' => 'integer',
            'currentUsages' => 'integer',
        ];
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'promotion_services', 'promotionId', 'serviceId');
    }

    public function promotionServices()
    {
        return $this->hasMany(PromotionService::class, 'promotionId');
    }

    public function usages()
    {
        return $this->hasMany(PromotionUsage::class, 'promotionId');
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now()->toDateString();

        if ($this->startDate && $now < $this->startDate) {
            return false;
        }

        if ($this->endDate && $now > $this->endDate) {
            return false;
        }

        if ($this->maxUsages && $this->currentUsages >= $this->maxUsages) {
            return false;
        }

        return true;
    }
}
