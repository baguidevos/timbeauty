<?php

namespace App\Filament\Widgets;

use App\Models\SaleItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TopServicesWidget extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Top 5 prestations du mois';

    protected string $color = 'primary';

    public function getData(): array
    {
        $monthStart = Carbon::now()->startOfMonth()->toDateString();

        $topServices = SaleItem::query()
            ->selectRaw('name, SUM(quantity) as total_count, SUM(total) as total_revenue')
            ->where('type', 'service')
            ->whereHas('sale', function ($query) use ($monthStart) {
                $query->where('status', 'completed')
                    ->whereDate('created_at', '>=', $monthStart);
            })
            ->groupBy('name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];

        foreach ($topServices as $service) {
            $labels[] = $service->name;
            $data[] = (float) $service->total_revenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenu (FCFA)',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(245, 158, 11, 0.70)',
                        'rgba(245, 158, 11, 0.55)',
                        'rgba(245, 158, 11, 0.40)',
                        'rgba(245, 158, 11, 0.25)',
                    ],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'callback' => 'function(value) { return (value / 1000).toFixed(0) + "k"; }',
                    ],
                ],
            ],
        ];
    }
}
