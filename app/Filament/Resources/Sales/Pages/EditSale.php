<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Product;
use App\Models\StockMovement;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected array $originalProductQuantities = [];

    protected function beforeSave(): void
    {
        // Snapshot existing product sold quantities
        $this->originalProductQuantities = [];
        foreach ($this->record->items as $item) {
            if ($item->type === 'product' && $item->itemId) {
                $effectiveSold = ($item->status === 'returned' || $item->status === 'cancelled') ? 0 : (int) $item->quantity;
                $this->originalProductQuantities[$item->itemId] = ($this->originalProductQuantities[$item->itemId] ?? 0) + $effectiveSold;
            }
        }
    }

    protected function afterSave(): void
    {
        $sale = $this->record->fresh(['items']);

        // Compute updated product quantities
        $newProductQuantities = [];
        foreach ($sale->items as $item) {
            if ($item->type === 'product' && $item->itemId) {
                $effectiveSold = ($item->status === 'returned' || $item->status === 'cancelled') ? 0 : (int) $item->quantity;
                $newProductQuantities[$item->itemId] = ($newProductQuantities[$item->itemId] ?? 0) + $effectiveSold;
            }
        }

        $allProductIds = array_unique(array_merge(
            array_keys($this->originalProductQuantities),
            array_keys($newProductQuantities)
        ));

        foreach ($allProductIds as $productId) {
            $oldQty = $this->originalProductQuantities[$productId] ?? 0;
            $newQty = $newProductQuantities[$productId] ?? 0;
            $delta = $newQty - $oldQty;

            if ($delta > 0) {
                // Additional products sold -> stock out
                $product = Product::find($productId);
                if ($product) {
                    $product->decrement('stockQuantity', $delta);
                    StockMovement::create([
                        'productId' => $product->id,
                        'type' => 'out',
                        'quantity' => $delta,
                        'reason' => "Modification vente #{$sale->id} : quantité augmentée (+{$delta})",
                        'reference' => "SALE-EDIT-{$sale->id}",
                    ]);
                }
            } elseif ($delta < 0) {
                // Products returned or reduced -> stock in
                $returnedQty = abs($delta);
                $product = Product::find($productId);
                if ($product) {
                    $product->increment('stockQuantity', $returnedQty);
                    StockMovement::create([
                        'productId' => $product->id,
                        'type' => 'in',
                        'quantity' => $returnedQty,
                        'reason' => "Modification vente #{$sale->id} : retour / réduction d'articles (-{$returnedQty})",
                        'reference' => "RETURN-SALE-{$sale->id}",
                    ]);
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
