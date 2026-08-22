<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\Widget;

class LowStockWidget extends Widget
{
    protected string $view = 'filament.widgets.low-stock-widget';

    protected int|string|array $columnSpan = 1;

    public array $lowStockProducts = [];

    public int $lowStockCount = 0;

    public function mount(): void
    {
        $this->loadLowStock();
    }

    public function loadLowStock(): void
    {
        $allLowStock = Product::where('status', 'active')
            ->lowStock()
            ->get();

        $this->lowStockCount = $allLowStock->count();
        $this->lowStockProducts = $allLowStock->take(5)->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'stock_quantity' => $p->stockQuantity,
            'min_stock_level' => $p->minStockLevel,
        ])->values()->toArray();
    }
}
