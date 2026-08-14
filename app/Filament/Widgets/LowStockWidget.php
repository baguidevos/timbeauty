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
        $products = Product::where('status', 'active')
            ->get()
            ->filter(fn ($p) => $p->stock_quantity <= $p->min_stock_level)
            ->take(5);

        $this->lowStockCount = $products->count();
        $this->lowStockProducts = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'stock_quantity' => $p->stock_quantity,
            'min_stock_level' => $p->min_stock_level,
        ])->values()->toArray();
    }
}
