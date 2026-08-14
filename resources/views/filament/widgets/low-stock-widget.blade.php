<x-filament-widgets::widget>
    <div class="rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 shadow-sm h-full">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-rose-500" />
                Alertes stock
            </h3>
            @if($lowStockCount > 0)
                <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                    {{ $lowStockCount }}
                </span>
            @endif
        </div>

        @if(count($lowStockProducts) > 0)
            <div class="space-y-2.5">
                @foreach($lowStockProducts as $product)
                    <div class="flex items-center gap-2.5">
                        <x-heroicon-m-exclamation-triangle class="h-4 w-4 text-rose-500 shrink-0" />
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate block">
                                {{ $product['name'] }}
                            </span>
                            <span class="text-xs text-gray-400">
                                Stock: {{ $product['stock_quantity'] }} / Min: {{ $product['min_stock_level'] }}
                            </span>
                        </div>
                        <span class="inline-flex items-center rounded-md bg-rose-50 px-1.5 py-0.5 text-[10px] font-semibold text-rose-600 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 shrink-0">
                            Critique
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-6 text-center text-gray-400 text-xs flex flex-col items-center gap-1.5">
                <span class="text-3xl">✅</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">Tous les stocks sont OK</p>
                <p class="text-[11px] text-gray-400">Aucun produit sous le seuil critique</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
