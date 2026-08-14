<x-filament-widgets::widget>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- 1. Today Revenue -->
        <div class="fi-card rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 border-l-4 border-l-emerald-500 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <x-heroicon-o-arrow-trending-up class="h-5 w-5" />
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Chiffre d'affaires du jour</span>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white tabular-nums">
                    {{ \App\Helpers\FormatHelper::formatFCFA($todayRevenue) }}
                </p>
                <div class="flex items-center justify-between mt-1 text-xs">
                    @php
                        $revChange = $yesterdayRevenue > 0 ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 : ($todayRevenue > 0 ? 100 : 0);
                        $isUp = $revChange >= 0;
                    @endphp
                    <span class="inline-flex items-center gap-0.5 font-medium {{ $isUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                        @if($isUp)
                            <x-heroicon-m-arrow-up-right class="h-3.5 w-3.5" /> +{{ number_format($revChange, 0) }}%
                        @else
                            <x-heroicon-m-arrow-down-right class="h-3.5 w-3.5" /> {{ number_format($revChange, 0) }}%
                        @endif
                        <span class="text-gray-400 font-normal">vs hier</span>
                    </span>
                </div>
                <!-- Mini Progress Bar -->
                @php $todayPct = min(($todayRevenue / max($dailyGoal, 1)) * 100, 100); @endphp
                <div class="mt-2.5 flex items-center gap-2">
                    <div class="flex-1 h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $todayPct }}%"></div>
                    </div>
                    <span class="text-[10px] text-gray-400 tabular-nums">{{ number_format($todayPct, 0) }}%</span>
                </div>
            </div>
        </div>

        <!-- 2. Month Revenue -->
        <div class="fi-card rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 border-l-4 border-l-amber-500 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 shrink-0">
                    <x-heroicon-o-chart-bar class="h-5 w-5" />
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">CA du mois</span>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white tabular-nums">
                    {{ \App\Helpers\FormatHelper::formatFCFA($monthRevenue) }}
                </p>
                <div class="mt-1 text-xs text-gray-400">
                    1er du mois — aujourd'hui
                </div>
                <!-- Mini Progress Bar -->
                @php $monthPct = min(($monthRevenue / max($monthlyGoal, 1)) * 100, 100); @endphp
                <div class="mt-2.5 flex items-center gap-2">
                    <div class="flex-1 h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-amber-500 transition-all duration-500" style="width: {{ $monthPct }}%"></div>
                    </div>
                    <span class="text-[10px] text-gray-400 tabular-nums">{{ number_format($monthPct, 0) }}%</span>
                </div>
            </div>
        </div>

        <!-- 3. Total Clients -->
        <div class="fi-card rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 border-l-4 border-l-sky-500 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 shrink-0">
                    <x-heroicon-o-user-group class="h-5 w-5" />
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Clients</span>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white tabular-nums">
                    {{ number_format($totalClients, 0, ',', ' ') }}
                </p>
                <div class="flex items-center justify-between mt-1 text-xs">
                    @php
                        $clientChange = $prevMonthClients > 0 ? (($newClientsThisMonth - $prevMonthClients) / $prevMonthClients) * 100 : ($newClientsThisMonth > 0 ? 100 : 0);
                        $isClientUp = $clientChange >= 0;
                    @endphp
                    <span class="inline-flex items-center gap-0.5 font-medium {{ $isClientUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                        @if($isClientUp)
                            <x-heroicon-m-arrow-up-right class="h-3.5 w-3.5" /> +{{ $newClientsThisMonth }}
                        @else
                            <x-heroicon-m-arrow-down-right class="h-3.5 w-3.5" /> {{ $newClientsThisMonth }}
                        @endif
                        <span class="text-gray-400 font-normal">nouveaux ce mois</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- 4. Today Appointments -->
        <div class="fi-card rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 border-l-4 border-l-violet-500 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 shrink-0">
                    <x-heroicon-o-calendar-days class="h-5 w-5" />
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Rendez-vous aujourd'hui</span>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white tabular-nums">
                    {{ $todayAppointments }}
                </p>
                <div class="mt-1 text-xs text-gray-400">
                    {{ $yesterdayAppointments }} RDV hier
                </div>
            </div>
        </div>

        <!-- 5. Today Expenses -->
        <div class="fi-card rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 border-l-4 {{ $todayExpenseTotal > 0 ? 'border-l-rose-500' : 'border-l-gray-300 dark:border-l-gray-700' }} shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $todayExpenseTotal > 0 ? 'bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' }} shrink-0">
                    <x-heroicon-o-receipt-percent class="h-5 w-5" />
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Dépenses du jour</span>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-bold tracking-tight {{ $todayExpenseTotal > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }} tabular-nums">
                    {{ $todayExpenseTotal > 0 ? '-' : '' }}{{ \App\Helpers\FormatHelper::formatFCFA($todayExpenseTotal) }}
                </p>
                <div class="mt-1 text-xs text-gray-400">
                    Total dépenses aujourd'hui
                </div>
            </div>
        </div>

        <!-- 6. Cash Register Balance -->
        <div class="fi-card rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 border-l-4 {{ $cashBalance >= 0 ? 'border-l-emerald-500' : 'border-l-rose-500 animate-pulse' }} shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $cashBalance >= 0 ? 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' }} shrink-0">
                        <x-heroicon-o-wallet class="h-5 w-5" />
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Solde caisse</span>
                </div>
                @if($cashBalance < 0)
                    <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                        <x-heroicon-m-exclamation-triangle class="h-3 w-3" /> Négatif
                    </span>
                @endif
            </div>
            <div class="mt-3">
                <p class="text-2xl font-bold tracking-tight {{ $cashBalance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} tabular-nums">
                    {{ \App\Helpers\FormatHelper::formatFCFA($cashBalance) }}
                </p>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                    @if($cashRegisterOpen)
                        <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-emerald-700 dark:text-emerald-300 font-medium">Caisse ouverte</span>
                    @else
                        <span class="inline-block h-2 w-2 rounded-full bg-rose-500"></span>
                        <span class="text-rose-700 dark:text-rose-300 font-medium">Caisse fermée</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
