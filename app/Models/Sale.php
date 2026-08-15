<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientId',
        'barberId',
        'subtotal',
        'discountAmount',
        'total',
        'paymentMethod',
        'status',
        'notes',
        'cashRegisterId',
        'createdBy',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:0',
            'discountAmount' => 'decimal:0',
            'total' => 'decimal:0',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'cashRegisterId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'saleId');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'saleId');
    }

    public function promotionUsages()
    {
        return $this->hasMany(PromotionUsage::class, 'saleId');
    }

    public function loyaltyPointTransactions()
    {
        return $this->hasMany(LoyaltyPointTransaction::class, 'saleId');
    }

    protected static function booted(): void
    {
        static::creating(function (Sale $sale): void {
            if (! $sale->cashRegisterId) {
                $openRegister = CashRegister::where('status', 'open')->first();
                if ($openRegister) {
                    $sale->cashRegisterId = $openRegister->id;
                }
            }
        });

        static::created(function (Sale $sale): void {
            if ($sale->paymentMethod === 'cash' && $sale->cashRegisterId) {
                CashTransaction::create([
                    'cashRegisterId' => $sale->cashRegisterId,
                    'type' => 'sale',
                    'amount' => $sale->total,
                    'description' => 'Encaissement Vente / Prestation #'.$sale->id,
                    'referenceId' => (string) $sale->id,
                    'createdBy' => $sale->createdBy ?? auth()->id(),
                ]);
            }
        });
    }
}
