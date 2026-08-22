<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, LogsActivity;

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

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stockQuantity', '<=', 'minStockLevel');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stockQuantity', '<=', 0);
    }

    public function getActivityDescription(string $action): string
    {
        return match ($action) {
            'create' => "Nouveau produit ajouté au stock : {$this->name} (Qté : {$this->stockQuantity})",
            'update' => "Mise à jour du produit : {$this->name} (Stock actuel : {$this->stockQuantity})",
            'delete' => "Suppression du produit : {$this->name}",
            default => "Action {$action} sur le produit {$this->name}",
        };
    }
}
