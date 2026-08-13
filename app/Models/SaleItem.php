<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'saleId',
        'type',
        'itemId',
        'name',
        'quantity',
        'unitPrice',
        'discount',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unitPrice' => 'decimal:0',
            'discount' => 'decimal:0',
            'total' => 'decimal:0',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'saleId');
    }

    public function service()
    {
        return $this->type === 'service'
            ? Service::find($this->itemId)
            : null;
    }

    public function product()
    {
        return $this->type === 'product'
            ? Product::find($this->itemId)
            : null;
    }
}
