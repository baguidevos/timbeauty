<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'reference',
        'categoryId',
        'brand',
        'purchasePrice',
        'sellingPrice',
        'stockQuantity',
        'minStockLevel',
        'supplier',
        'supplierId',
        'photo',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'purchasePrice' => 'decimal:0',
            'sellingPrice' => 'decimal:0',
            'stockQuantity' => 'integer',
            'minStockLevel' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'categoryId');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierId');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'productId');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'productId');
    }

    public function isLowStock(): bool
    {
        return $this->stockQuantity <= $this->minStockLevel;
    }
}
