<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchaseOrderId',
        'productId',
        'productName',
        'quantity',
        'unitPrice',
        'totalPrice',
        'receivedQuantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unitPrice' => 'decimal:0',
            'totalPrice' => 'decimal:0',
            'receivedQuantity' => 'integer',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchaseOrderId');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }

    public function isFullyReceived(): bool
    {
        return $this->receivedQuantity >= $this->quantity;
    }
}
