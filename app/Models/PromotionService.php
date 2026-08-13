<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionService extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotionId',
        'serviceId',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotionId');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'serviceId');
    }
}
