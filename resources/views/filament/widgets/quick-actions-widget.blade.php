<x-filament-widgets::widget>
    <div class="rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 shadow-sm">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Actions rapides</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <!-- 1. Client -->
            <a href="{{ route('filament.admin.resources.clients.index') }}" wire:navigate class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200/80 dark:border-gray-800 hover:border-amber-400 dark:hover:border-amber-600 hover:bg-amber-50/50 dark:hover:bg-amber-950/20 transition-all group">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-user-plus class="h-5 w-5" />
                </div>
                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-2">Nouveau client</span>
            </a>

            <!-- 2. RDV -->
            <a href="{{ route('filament.admin.resources.appointments.index') }}" wire:navigate class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200/80 dark:border-gray-800 hover:border-emerald-400 dark:hover:border-emerald-600 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 transition-all group">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-calendar-days class="h-5 w-5" />
                </div>
                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-2">Nouveau RDV</span>
            </a>

            <!-- 3. Vente -->
            <a href="{{ route('filament.admin.pages.pos') }}" wire:navigate class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200/80 dark:border-gray-800 hover:border-sky-400 dark:hover:border-sky-600 hover:bg-sky-50/50 dark:hover:bg-sky-950/20 transition-all group">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-shopping-cart class="h-5 w-5" />
                </div>
                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-2">Nouvelle vente</span>
            </a>

            <!-- 4. Caisse -->
            <a href="{{ route('filament.admin.finance.resources.cash-registers.index') }}" wire:navigate class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200/80 dark:border-gray-800 hover:border-violet-400 dark:hover:border-violet-600 hover:bg-violet-50/50 dark:hover:bg-violet-950/20 transition-all group">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-wallet class="h-5 w-5" />
                </div>
                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-2">Ouvrir la caisse</span>
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
