<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientId',
        'points',
        'type',
        'description',
        'saleId',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'saleId');
    }

    public function isEarn(): bool
    {
        return $this->type === 'earn';
    }

    public function isRedeem(): bool
    {
        return $this->type === 'redeem';
    }
}
