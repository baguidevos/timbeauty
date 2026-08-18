<x-filament-panels::page>
    <div class="space-y-6">
        <!-- 1. KPI Overview -->
        @livewire(\App\Filament\Widgets\KpiOverviewWidget::class)

        <!-- 2. Charts & Targets -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-7">
                @livewire(\App\Filament\Widgets\RevenueChartWidget::class)
            </div>
            <div class="lg:col-span-5">
                @livewire(\App\Filament\Widgets\RevenueTargetWidget::class)
            </div>
        </div>

        <!-- 3. Top Rankings (Services & Barbers) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <div>
                @livewire(\App\Filament\Widgets\TopServicesWidget::class)
            </div>
            <div>
                @livewire(\App\Filament\Widgets\TopBarbersWidget::class)
            </div>
        </div>

        <!-- 4. Recent Transactions (Sales & Expenses) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <div>
                @livewire(\App\Filament\Widgets\RecentSalesWidget::class)
            </div>
            <div>
                @livewire(\App\Filament\Widgets\RecentExpensesWidget::class)
            </div>
        </div>
    </div>
</x-filament-panels::page>
