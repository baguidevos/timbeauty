<x-filament-widgets::widget>
    <div class="rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 shadow-sm h-full">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <x-heroicon-o-sparkles class="h-5 w-5 text-amber-500" />
            Top coiffeurs du mois
        </h3>

        @if(count($topBarbers) > 0)
            <div class="space-y-3">
                @foreach($topBarbers as $index => $barber)
                    <div class="flex items-center gap-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-xs font-bold shrink-0">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate block">
                                {{ $barber['name'] }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ $barber['sales_count'] }} vente{{ $barber['sales_count'] > 1 ? 's' : '' }}
                            </span>
                        </div>
                        <span class="text-sm font-semibold text-amber-600 dark:text-amber-400 tabular-nums">
                            {{ \App\Helpers\FormatHelper::formatFCFA($barber['revenue']) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-6 text-center text-gray-400 text-xs flex flex-col items-center gap-1.5">
                <span class="text-3xl">💈</span>
                <p class="font-medium">Aucune donnée ce mois</p>
                <p class="text-[11px] text-gray-400">Les ventes réapparaîtront ici</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
