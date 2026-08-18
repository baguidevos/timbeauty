<x-filament-panels::page>
    <div class="space-y-6">
        <!-- 1. Top Tab Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-800 pb-3">
            <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-1 shrink-0 shadow-2xs">
                <button
                    type="button"
                    wire:click="$set('activeTab', 'daily')"
                    class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg transition-all {{ $activeTab === 'daily' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                >
                    <x-heroicon-m-sun class="h-4 w-4 text-amber-500" />
                    <span>Quotidien & En direct</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('activeTab', 'finance')"
                    class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg transition-all {{ $activeTab === 'finance' ? 'bg-white dark:bg-gray-900 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                >
                    <x-heroicon-m-banknotes class="h-4 w-4 text-emerald-500" />
                    <span>Finances & Revenus</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('activeTab', 'team')"
                    class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg transition-all {{ $activeTab === 'team' ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                >
                    <x-heroicon-m-user-group class="h-4 w-4 text-indigo-500" />
                    <span>Équipe & Prestations</span>
                </button>
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                @if($activeTab === 'daily')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-medium">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Vue Opérationnelle (Aujourd'hui)
                    </span>
                @elseif($activeTab === 'finance')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-medium">
                        <x-heroicon-m-arrow-trending-up class="h-3.5 w-3.5 text-emerald-500" />
                        Performance Financière & Trésorerie
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-medium">
                        <x-heroicon-m-sparkles class="h-3.5 w-3.5 text-indigo-500" />
                        Activité Métier & Statistiques Salon
                    </span>
                @endif
            </div>
        </div>

        <!-- 2. TAB 1: DAILY / QUOTIDIEN -->
        @if($activeTab === 'daily')
            <div class="space-y-6">
                <!-- Greeting & Weather -->
                @livewire(\App\Filament\Widgets\GreetingWidget::class)

                <!-- Quick Action Buttons -->
                @livewire(\App\Filament\Widgets\QuickActionsWidget::class)

                <!-- 6 Main KPI Cards -->
                @livewire(\App\Filament\Widgets\KpiOverviewWidget::class)

                <!-- Daily Operations Grid (Next Appointment + Live Alerts) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <!-- Left: Next Appointment Hero -->
                    <div class="lg:col-span-7">
                        @livewire(\App\Filament\Widgets\NextAppointmentWidget::class)
                    </div>

                    <!-- Right: Alerts (Low stock + Birthdays) -->
                    <div class="lg:col-span-5 space-y-6">
                        @livewire(\App\Filament\Widgets\LowStockWidget::class)
                        @livewire(\App\Filament\Widgets\BirthdayRemindersWidget::class)
                    </div>
                </div>
            </div>

        <!-- 3. TAB 2: FINANCE & REVENUS -->
        @elseif($activeTab === 'finance')
            <div class="space-y-6">
                <!-- Target & Profit Row -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <div class="lg:col-span-7">
                        @livewire(\App\Filament\Widgets\RevenueTargetWidget::class)
                    </div>
                    <div class="lg:col-span-5">
                        @livewire(\App\Filament\Widgets\EstimatedProfitWidget::class)
                    </div>
                </div>

                <!-- Charts & Expenses Row -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <div class="lg:col-span-7">
                        @livewire(\App\Filament\Widgets\RevenueChartWidget::class)
                    </div>
                    <div class="lg:col-span-5">
                        @livewire(\App\Filament\Widgets\RecentExpensesWidget::class)
                    </div>
                </div>

                <!-- Recent Sales Table -->
                <div>
                    @livewire(\App\Filament\Widgets\RecentSalesWidget::class)
                </div>
            </div>

        <!-- 4. TAB 3: ÉQUIPE & PRESTATIONS -->
        @else
            <div class="space-y-6">
                <!-- Team & Services Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <div>
                        @livewire(\App\Filament\Widgets\TopBarbersWidget::class)
                    </div>
                    <div>
                        @livewire(\App\Filament\Widgets\TopServicesWidget::class)
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
