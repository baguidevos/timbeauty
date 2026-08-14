<x-filament-panels::page>
    <div class="space-y-6">
        <!-- 1. Greeting Section -->
        @livewire(\App\Filament\Widgets\GreetingWidget::class)

        <!-- 2. 6 Main KPI Cards -->
        @livewire(\App\Filament\Widgets\KpiOverviewWidget::class)

        <!-- 3. Estimated Daily Profit Banner -->
        @livewire(\App\Filament\Widgets\EstimatedProfitWidget::class)

        <!-- 4. Monthly Revenue Target Radial Gauge -->
        @livewire(\App\Filament\Widgets\RevenueTargetWidget::class)

        <!-- 5. Charts Grid (7-Day Revenue Trend + Top 5 Services) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @livewire(\App\Filament\Widgets\RevenueChartWidget::class)
            @livewire(\App\Filament\Widgets\TopServicesWidget::class)
        </div>

        <!-- 6. Next Appointment Hero Countdown -->
        @livewire(\App\Filament\Widgets\NextAppointmentWidget::class)

        <!-- 7. Quick Actions Grid -->
        @livewire(\App\Filament\Widgets\QuickActionsWidget::class)

        <!-- 8. Bottom Widgets Row (Top Barbers, Low Stock, Recent Expenses) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @livewire(\App\Filament\Widgets\TopBarbersWidget::class)
            @livewire(\App\Filament\Widgets\LowStockWidget::class)
            @livewire(\App\Filament\Widgets\RecentExpensesWidget::class)
        </div>

        <!-- 9. Client Birthday Reminders -->
        @livewire(\App\Filament\Widgets\BirthdayRemindersWidget::class)
    </div>
</x-filament-panels::page>
