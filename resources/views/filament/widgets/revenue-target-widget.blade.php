<x-filament-widgets::widget>
    @php
        $strokeColor = $isReached || $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
        $badgeClass = $isReached || $pct >= 80 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300' : ($pct >= 50 ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300');
        $badgeLabel = $isReached ? 'Objectif atteint' : ($pct >= 80 ? 'Excellent' : ($pct >= 50 ? 'En progrès' : 'En dessous'));
        
        $r = 36;
        $circumference = 2 * M_PI * $r;
        $dashOffset = $circumference - ($pct / 100) * $circumference;
    @endphp

    <div class="rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 border-l-4 border-l-amber-500 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <x-heroicon-o-trophy class="h-5 w-5 text-amber-500" />
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Objectif du mois</h3>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border {{ $badgeClass }}">
                    {{ $badgeLabel }}
                </span>
            </div>
            @if(!$isReached)
                <span class="text-xs text-gray-400">
                    Reste {{ \App\Helpers\FormatHelper::formatFCFA($remaining) }}
                </span>
            @endif
        </div>

        <div class="mt-4 flex items-center gap-6">
            <!-- Radial Gauge SVG -->
            <div class="relative shrink-0 flex items-center justify-center">
                <svg width="80" height="80" viewBox="0 0 80 80" class="-rotate-90">
                    <circle cx="40" cy="40" r="{{ $r }}" fill="none" stroke="currentColor" stroke-width="6" class="text-gray-100 dark:text-gray-800" />
                    <circle cx="40" cy="40" r="{{ $r }}" fill="none" stroke="{{ $strokeColor }}" stroke-width="6" stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $dashOffset }}" class="transition-all duration-1000 ease-out" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    @if($isReached)
                        <x-heroicon-s-sparkles class="h-6 w-6 text-emerald-500 animate-bounce" />
                    @else
                        <span class="text-base font-bold tabular-nums text-gray-900 dark:text-white">
                            {{ number_format($pct, 0) }}%
                        </span>
                    @endif
                </div>
            </div>

            <!-- Details -->
            <div class="flex-1 min-w-0">
                @if($isReached)
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 shrink-0">
                            <x-heroicon-o-sparkles class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Objectif atteint ! 🎉</p>
                            <p class="text-xs text-gray-400">
                                {{ \App\Helpers\FormatHelper::formatFCFA($monthRevenue) }} sur {{ \App\Helpers\FormatHelper::formatFCFA($targetAmount) }}
                            </p>
                        </div>
                    </div>
                @else
                    <div>
                        <div class="flex items-baseline gap-2 flex-wrap">
                            <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white tabular-nums">
                                {{ \App\Helpers\FormatHelper::formatFCFA($monthRevenue) }}
                            </span>
                            <span class="text-xs text-gray-400">
                                sur {{ \App\Helpers\FormatHelper::formatFCFA($targetAmount) }}
                            </span>
                        </div>
                        <div class="mt-2.5 h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%; background-color: {{ $strokeColor }};"></div>
                        </div>
                    </div>
                @endif

                <div class="mt-3 flex items-center gap-4 text-xs text-gray-400">
                    <span>Objectif : <strong class="text-gray-900 dark:text-white font-medium">{{ \App\Helpers\FormatHelper::formatFCFA($targetAmount) }}</strong></span>
                    @if($isReached)
                        <span class="font-medium text-emerald-600 dark:text-emerald-400">+{{ \App\Helpers\FormatHelper::formatFCFA($monthRevenue - $targetAmount) }} au-delà</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
