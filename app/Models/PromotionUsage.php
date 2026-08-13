<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotionId',
        'clientId',
        'saleId',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotionId');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'saleId');
    }
}
