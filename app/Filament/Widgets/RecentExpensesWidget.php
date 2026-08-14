<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatHelper;
use App\Models\Expense;
use Filament\Widgets\Widget;

class RecentExpensesWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-expenses-widget';

    protected int|string|array $columnSpan = 1;

    public array $recentExpenses = [];

    public function mount(): void
    {
        $this->loadRecentExpenses();
    }

    public function loadRecentExpenses(): void
    {
        $expenses = Expense::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $paymentMethods = [
            'cash' => 'Espèces',
            'tmoney' => 'TMoney',
            'flooz' => 'Flooz',
            'card' => 'Carte',
            'transfer' => 'Virement',
            'other' => 'Autre',
        ];

        $this->recentExpenses = $expenses->map(function ($e) use ($paymentMethods) {
            return [
                'id' => $e->id,
                'amount' => (float) $e->amount,
                'description' => $e->description ?: ($e->category?->name ?? 'Dépense'),
                'payment_method' => $paymentMethods[$e->payment_method] ?? $e->payment_method,
                'date' => FormatHelper::formatDate($e->date),
            ];
        })->toArray();
    }
}
