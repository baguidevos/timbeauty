<?php

namespace App\Filament\Resources\Payrolls\Widgets;

use App\Models\Payroll;
use Filament\Widgets\ChartWidget;

class PayrollChartWidget extends ChartWidget
{
    protected ?string $heading = 'Salaires nets & Paiements par employé (Mois en cours)';

    protected ?string $maxHeight = '200px';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getData(): array
    {
        $currentMonth = (int) now()->format('m');
        $currentYear = (int) now()->format('Y');

        $payrolls = Payroll::with(['barber', 'salaryPayments'])
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get();

        $labels = [];
        $netData = [];
        $paidData = [];

        foreach ($payrolls as $p) {
            $name = $p->barber ? "{$p->barber->firstName} {$p->barber->lastName}" : 'Inconnu';
            $labels[] = $name;
            $netData[] = (float) $p->netSalary;
            $paidData[] = (float) $p->total_paid;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Salaire net dû (FCFA)',
                    'data' => $netData,
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#d97706',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'maxBarThickness' => 100,
                ],
                [
                    'label' => 'Montant déjà payé (FCFA)',
                    'data' => $paidData,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#059669',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'maxBarThickness' => 28,
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
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": " + Number(context.parsed.y).toLocaleString("fr-FR") + " FCFA"; }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return (value / 1000).toLocaleString("fr-FR") + " k"; }',
                    ],
                ],
            ],
        ];
    }
}
