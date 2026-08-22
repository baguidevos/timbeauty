<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivityDescription(string $action): string
    {
        $productName = $this->product?->name ?? "Produit #{$this->productId}";
        $typeLabel = $this->type === 'in' ? 'Entrée (+)' : 'Sortie (-)';
        $reason = $this->reason ? " : {$this->reason}" : '';

        return match ($action) {
            'create' => "Mouvement de stock ({$typeLabel} {$this->quantity}) sur {$productName}{$reason}",
            'update' => "Modification du mouvement de stock #{$this->id} ({$productName})",
            'delete' => "Suppression du mouvement de stock #{$this->id} ({$productName})",
            default => "Action {$action} sur mouvement de stock #{$this->id}",
        };
    }
}
