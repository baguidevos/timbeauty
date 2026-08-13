<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'productId',
        'type',
        'quantity',
        'reason',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }

    public function isIn(): bool
    {
        return $this->type === 'in';
    }

    public function isOut(): bool
    {
        return $this->type === 'out';
    }
}
