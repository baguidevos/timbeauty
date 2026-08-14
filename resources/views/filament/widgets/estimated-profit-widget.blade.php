<x-filament-widgets::widget>
    <div class="rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 border-l-4 {{ $estimatedProfit >= 0 ? 'border-l-emerald-500' : 'border-l-rose-500' }} shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $estimatedProfit >= 0 ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-md shadow-emerald-500/20' : 'bg-gradient-to-br from-rose-400 to-rose-600 shadow-md shadow-rose-500/20' }} text-white shrink-0">
                    <x-heroicon-o-banknotes class="h-6 w-6" />
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Bénéfice estimé du jour</span>
                    <p class="text-3xl font-bold tracking-tight {{ $estimatedProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} tabular-nums">
                        {{ \App\Helpers\FormatHelper::formatFCFA($estimatedProfit) }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-6 border-t sm:border-t-0 sm:border-l border-gray-100 dark:border-gray-800 pt-3 sm:pt-0 sm:pl-6">
                <div class="flex flex-col">
                    <span class="text-xs text-gray-400">Chiffre d'affaires</span>
                    <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums">
                        {{ \App\Helpers\FormatHelper::formatFCFA($todayRevenue) }}
                    </span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs text-gray-400">Dépenses</span>
                    <span class="text-sm font-semibold {{ $todayExpenses > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }} tabular-nums">
                        {{ $todayExpenses > 0 ? '-' : '' }}{{ \App\Helpers\FormatHelper::formatFCFA($todayExpenses) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
