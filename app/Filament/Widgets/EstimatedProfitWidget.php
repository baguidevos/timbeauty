<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Sale;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class EstimatedProfitWidget extends Widget
{
    protected string $view = 'filament.widgets.estimated-profit-widget';

    protected int|string|array $columnSpan = 'full';

    public float $todayRevenue = 0;

    public float $todayExpenses = 0;

    public float $estimatedProfit = 0;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $today = Carbon::today()->toDateString();

        $this->todayRevenue = (float) Sale::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('total');

        $this->todayExpenses = (float) Expense::whereDate('date', $today)->sum('amount');

        $this->estimatedProfit = $this->todayRevenue - $this->todayExpenses;
    }
}
