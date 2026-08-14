<x-filament-widgets::widget>
    <div class="glass-card-premium flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent p-5 border border-amber-500/20 dark:border-amber-500/10 shadow-sm relative overflow-hidden">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-12 -left-12 w-40 h-40 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-4 relative z-10">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-md shadow-amber-500/20 shrink-0">
                <x-heroicon-o-sparkles class="h-6 w-6 animate-pulse" />
            </div>
            <div>
                <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <span>{{ $greeting }}</span>
                    <span class="inline-block animate-bounce">👋</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 capitalize mt-0.5">
                    {{ $dayLabel }} — {{ $timeLabel }}
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:items-end gap-1.5 relative z-10">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-md px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200/50 dark:border-gray-700/50 shadow-xs">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>Dernière mise à jour · <strong class="text-gray-900 dark:text-white">{{ $updatedAt }}</strong></span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">
                Aperçu en temps réel de votre salon
            </p>
        </div>
    </div>
</x-filament-widgets::widget>
