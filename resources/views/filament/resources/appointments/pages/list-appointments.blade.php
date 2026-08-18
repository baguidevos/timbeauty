<x-filament-panels::page>
    <div class="space-y-6">
        <!-- 1. Top Tab Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-800 pb-3">
            <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-1 shrink-0 shadow-2xs">
                <button
                    type="button"
                    wire:click="$set('viewTab', 'planner')"
                    class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg transition-all {{ $viewTab === 'planner' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                >
                    <x-heroicon-m-calendar-days class="h-4 w-4 text-amber-500" />
                    <span>Planning & Agenda</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('viewTab', 'table')"
                    class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg transition-all {{ $viewTab === 'table' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                >
                    <x-heroicon-m-table-cells class="h-4 w-4 text-amber-500" />
                    <span>Tableau & Statistiques</span>
                </button>
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                @if($viewTab === 'planner')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-medium">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Vue Calendrier interactive
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium">
                        <x-heroicon-m-funnel class="h-3.5 w-3.5 text-gray-500" />
                        Vue Liste détaillée & Filtres
                    </span>
                @endif
            </div>
        </div>

        <!-- 2. Tab Content -->
        @if($viewTab === 'planner')
            <!-- TAB 1: Guava Interactive Calendar Widget -->
            @livewire(\App\Filament\Resources\Appointments\Widgets\AppointmentCalendarWidget::class)
        @else
            <!-- TAB 2: Quick KPI Stat Cards + Table -->
            @php
                $stats = $this->getStats();
            @endphp

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-filament::section compact>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <x-heroicon-o-calendar-days class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Rendez-vous</div>
                            <div class="text-xl font-extrabold text-gray-950 dark:text-white">{{ $stats['total'] }}</div>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section compact>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400">
                            <x-heroicon-o-clock class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Aujourd'hui</div>
                            <div class="text-xl font-extrabold text-gray-950 dark:text-white">{{ $stats['today'] }}</div>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section compact>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <x-heroicon-o-check-circle class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Terminés</div>
                            <div class="text-xl font-extrabold text-gray-950 dark:text-white">{{ $stats['completed'] }}</div>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section compact>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                            <x-heroicon-o-x-circle class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Annulés / Absents</div>
                            <div class="text-xl font-extrabold text-gray-950 dark:text-white">{{ $stats['cancelled'] }}</div>
                        </div>
                    </div>
                </x-filament::section>
            </div>

            <!-- Full Filament Table -->
            <div class="space-y-4">
                {{ $this->table }}
            </div>
        @endif
    </div>
</x-filament-panels::page>
