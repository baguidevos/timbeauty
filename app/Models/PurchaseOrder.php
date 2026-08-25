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

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->totalAmount - (float) $this->paidAmount);
    }

    public function getPaymentStatusAttribute(): string
    {
        if ((float) $this->paidAmount <= 0) {
            return 'unpaid';
        }

        if ((float) $this->paidAmount >= (float) $this->totalAmount) {
            return 'paid';
        }

        return 'partially_paid';
    }

    public function getPaymentPercentageAttribute(): int
    {
        if ((float) $this->totalAmount <= 0) {
            return 100;
        }

        return (int) min(100, round(((float) $this->paidAmount / (float) $this->totalAmount) * 100));
    }

    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partially_paid';
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === 'unpaid';
    }

    public function recordPayment(float $amount): void
    {
        $newPaid = (float) $this->paidAmount + $amount;
        $this->update([
            'paidAmount' => min((float) $this->totalAmount, max(0, $newPaid)),
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isPartiallyReceived(): bool
    {
        return $this->status === 'partially_received';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
