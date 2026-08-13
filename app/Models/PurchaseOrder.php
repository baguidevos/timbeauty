<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'supplierId',
        'orderDate',
        'expectedDate',
        'receivedDate',
        'status',
        'totalAmount',
        'paidAmount',
        'notes',
        'createdBy',
    ];

    protected function casts(): array
    {
        return [
            'orderDate' => 'date',
            'expectedDate' => 'date',
            'receivedDate' => 'date',
            'totalAmount' => 'decimal:0',
            'paidAmount' => 'decimal:0',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchaseOrderId');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
