<x-filament-widgets::widget>
    <div class="rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 shadow-sm h-full">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <x-heroicon-o-receipt-percent class="h-5 w-5 text-gray-500" />
            Dépenses récentes
        </h3>

        @if(count($recentExpenses) > 0)
            <div class="space-y-2.5">
                @foreach($recentExpenses as $expense)
                    <div class="flex items-center gap-2.5">
                        <x-heroicon-o-receipt-percent class="h-4 w-4 text-gray-400 shrink-0" />
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate block">
                                {{ $expense['description'] }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ $expense['date'] }} · {{ $expense['payment_method'] }}
                            </span>
                        </div>
                        <span class="text-sm font-semibold text-rose-600 dark:text-rose-400 whitespace-nowrap tabular-nums">
                            -{{ \App\Helpers\FormatHelper::formatFCFA($expense['amount']) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-6 text-center text-gray-400 text-xs flex flex-col items-center gap-1.5">
                <span class="text-3xl opacity-60">🧾</span>
                <p class="font-medium">Aucune dépense récente</p>
                <p class="text-[11px] text-gray-400">Les dépenses enregistrées apparaîtront ici</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
