<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'categoryId',
        'commissionRate',
        'status',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:0',
            'duration' => 'integer',
            'commissionRate' => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'categoryId');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'serviceId');
    }

    public function promotionServices()
    {
        return $this->hasMany(PromotionService::class, 'serviceId');
    }

    public function loyaltyRules()
    {
        return $this->hasMany(LoyaltyRule::class, 'serviceId');
    }
}
