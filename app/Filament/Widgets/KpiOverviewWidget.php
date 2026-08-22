<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\CashRegister;
use App\Models\Client;
use App\Models\Expense;
use App\Models\RevenueTarget;
use App\Models\Sale;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class KpiOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.kpi-overview-widget';

    protected int|string|array $columnSpan = 'full';

    public float $todayRevenue = 0;

    public float $yesterdayRevenue = 0;

    public float $monthRevenue = 0;

    public float $todayDiscountTotal = 0;

    public float $monthDiscountTotal = 0;

    public int $todayAppointments = 0;

    public int $yesterdayAppointments = 0;

    public int $totalClients = 0;

    public int $newClientsThisMonth = 0;

    public int $prevMonthClients = 0;

    public float $todayExpenseTotal = 0;

    public float $cashBalance = 0;

    public bool $cashRegisterOpen = false;

    public float $dailyGoal = 50000;

    public float $monthlyGoal = 1000000;

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $prevMonthStart = $now->copy()->subMonth()->startOfMonth()->toDateString();
        $prevMonthEnd = $now->copy()->subMonth()->endOfMonth()->toDateString();

        // 1. Revenues
        $this->todayRevenue = (float) Sale::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('total');

        $this->yesterdayRevenue = (float) Sale::where('status', 'completed')
            ->whereDate('created_at', $yesterday)
            ->sum('total');

        $this->monthRevenue = (float) Sale::where('status', 'completed')
            ->whereDate('created_at', '>=', $monthStart)
            ->sum('total');

        $this->todayDiscountTotal = (float) Sale::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('discountAmount');

        $this->monthDiscountTotal = (float) Sale::where('status', 'completed')
            ->whereDate('created_at', '>=', $monthStart)
            ->sum('discountAmount');

        // Target for the month
        $targetModel = RevenueTarget::where('type', 'shop')
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->first();

        if ($targetModel && $targetModel->targetAmount > 0) {
            $this->monthlyGoal = (float) $targetModel->targetAmount;
        }

        // 2. Clients
        $this->totalClients = Client::count();

        $this->newClientsThisMonth = Client::where('firstVisitDate', '>=', $monthStart)->count();

        $this->prevMonthClients = Client::whereBetween('firstVisitDate', [$prevMonthStart, $prevMonthEnd])->count();

        // 3. Appointments
        $this->todayAppointments = Appointment::whereDate('date', $today)->count();
        $this->yesterdayAppointments = Appointment::whereDate('date', $yesterday)->count();

        // 4. Expenses today
        $this->todayExpenseTotal = (float) Expense::whereDate('date', $today)->sum('amount');

        // 5. Cash Register Balance
        $openRegister = CashRegister::where('status', 'open')->first();
        if ($openRegister) {
            $this->cashRegisterOpen = true;
            $transactions = $openRegister->transactions;
            $totalIn = $transactions->whereIn('type', ['sale', 'deposit'])->sum('amount');
            $totalOut = $transactions->whereIn('type', ['expense', 'withdrawal'])->sum('amount');
            $this->cashBalance = (float) ($openRegister->openingAmount + $totalIn - $totalOut);
        } else {
            $this->cashRegisterOpen = false;
            $this->cashBalance = 0;
        }
    }
}
