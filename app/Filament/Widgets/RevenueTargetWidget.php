<?php

namespace App\Filament\Widgets;

use App\Models\RevenueTarget;
use App\Models\Sale;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class RevenueTargetWidget extends Widget
{
    protected string $view = 'filament.widgets.revenue-target-widget';

    protected int|string|array $columnSpan = 'full';

    public float $monthRevenue = 0;

    public float $targetAmount = 1000000;

    public float $pct = 0;

    public float $remaining = 0;

    public bool $isReached = false;

    public bool $hasTarget = false;

    public function mount(): void
    {
        $this->loadTarget();
    }

    public function loadTarget(): void
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();

        $this->monthRevenue = (float) Sale::where('status', 'completed')
            ->whereDate('created_at', '>=', $monthStart)
            ->sum('total');

        $targetModel = RevenueTarget::where('type', 'shop')
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->first();

        if ($targetModel && $targetModel->targetAmount > 0) {
            $this->hasTarget = true;
            $this->targetAmount = (float) $targetModel->targetAmount;
        } else {
            $this->hasTarget = false;
            $this->targetAmount = 1000000;
        }

        $this->pct = $this->targetAmount > 0 ? min(($this->monthRevenue / $this->targetAmount) * 100, 100) : 0;
        $this->remaining = max($this->targetAmount - $this->monthRevenue, 0);
        $this->isReached = $this->monthRevenue >= $this->targetAmount;
    }
}
