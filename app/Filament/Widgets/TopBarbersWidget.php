<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class TopBarbersWidget extends Widget
{
    protected string $view = 'filament.widgets.top-barbers-widget';

    protected int|string|array $columnSpan = 1;

    public array $topBarbers = [];

    public function mount(): void
    {
        $this->loadTopBarbers();
    }

    public function loadTopBarbers(): void
    {
        $monthStart = Carbon::now()->startOfMonth()->toDateString();

        $sales = Sale::with('barber')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $monthStart)
            ->whereNotNull('barberId')
            ->selectRaw('barberId, SUM(total) as revenue, COUNT(*) as sales_count')
            ->groupBy('barberId')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $this->topBarbers = $sales->map(function ($sale) {
            $name = $sale->barber ? "{$sale->barber->firstName} {$sale->barber->lastName}" : 'Coiffeur inconnu';

            return [
                'name' => $name,
                'revenue' => (float) $sale->revenue,
                'sales_count' => (int) $sale->sales_count,
            ];
        })->toArray();
    }
}
