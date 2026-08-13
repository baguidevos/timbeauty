<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class TopServicesWidget extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Top 5 services';

    protected string $color = 'primary';

    protected int|string|array $columnSpan = 'half';

    public function getData(): array
    {
        $services = Appointment::query()
            ->selectRaw('serviceId, COUNT(*) as count')
            ->with('service')
            ->groupBy('serviceId')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];

        foreach ($services as $appointment) {
            $labels[] = $appointment->service?->name ?? 'Inconnu';
            $data[] = $appointment->count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de rendez-vous',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(245, 158, 11, 0.55)',
                        'rgba(245, 158, 11, 0.4)',
                        'rgba(245, 158, 11, 0.3)',
                        'rgba(245, 158, 11, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(245, 158, 11, 1)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(245, 158, 11, 0.6)',
                        'rgba(245, 158, 11, 0.5)',
                        'rgba(245, 158, 11, 0.4)',
                    ],
                    'borderWidth' => 1,
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
        ];
    }
}
