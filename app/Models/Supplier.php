<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contactName',
        'phone',
        'email',
        'address',
        'paymentTerms',
        'notes',
        'status',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'supplierId');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplierId');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
