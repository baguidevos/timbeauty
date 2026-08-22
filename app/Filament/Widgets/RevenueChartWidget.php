<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Revenus des 7 derniers jours';

    protected string $color = 'warning';

    protected int|string|array $columnSpan = 'full';

    public function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->isoFormat('ddd dd/MM');
            $data[] = (float) Sale::where('status', 'completed')
                ->whereDate('created_at', $date->toDateString())
                ->sum('total');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenu (FCFA)',
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.3,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.parsed.y.toLocaleString("fr-FR") + " FCFA"; }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => 'function(value) { return value.toLocaleString("fr-FR") + " FCFA"; }',
                    ],
                ],
            ],
        ];
    }
}
