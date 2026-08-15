<?php

namespace App\Services;

use App\Models\Barber;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\Payroll;
use App\Models\SalaryPayment;
use App\Models\Sale;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Calculate or recalculate payroll for a single staff member for a specific month and year.
     */
    public function calculateForBarber(
        Barber $barber,
        int $month,
        int $year,
        ?float $bonus = null,
        ?float $advances = null,
        ?float $deductions = null
    ): Payroll {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Calculate commissions from sales completed in this month
        $sales = Sale::with('items')
            ->where('barberId', $barber->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $commissions = 0;
        if (in_array($barber->remunerationType, ['commission', 'fixed_plus_commission'])) {
            $rate = (float) ($barber->commissionRate ?? 0) / 100;
            foreach ($sales as $sale) {
                $serviceItems = $sale->items->where('type', 'service');
                foreach ($serviceItems as $item) {
                    $commissions += (float) $item->total * $rate;
                }
            }
        } elseif ($barber->remunerationType === 'per_service') {
            $serviceRate = (float) ($barber->perServiceRate ?? 0);
            $serviceCount = 0;
            foreach ($sales as $sale) {
                $serviceCount += $sale->items->where('type', 'service')->count();
            }
            $commissions = $serviceCount * $serviceRate;
        }

        $fixedSalary = (float) ($barber->fixedSalary ?? 0);

        // Find or instantiate existing payroll
        $payroll = Payroll::firstOrNew([
            'barberId' => $barber->id,
            'month' => $month,
            'year' => $year,
        ]);

        $bonusAmount = $bonus !== null ? $bonus : (float) ($payroll->bonus ?? 0);
        $advancesAmount = $advances !== null ? $advances : (float) ($payroll->advances ?? 0);
        $deductionsAmount = $deductions !== null ? $deductions : (float) ($payroll->deductions ?? 0);

        $netSalary = max(0, ($fixedSalary + $commissions + $bonusAmount) - ($advancesAmount + $deductionsAmount));

        $payroll->fixedSalary = $fixedSalary;
        $payroll->commissions = $commissions;
        $payroll->bonus = $bonusAmount;
        $payroll->advances = $advancesAmount;
        $payroll->deductions = $deductionsAmount;
        $payroll->netSalary = $netSalary;

        // Check if already fully paid or partially paid
        $totalPaid = $payroll->exists ? (float) $payroll->salaryPayments()->sum('amount') : 0;
        if ($totalPaid >= $netSalary && $netSalary > 0) {
            $payroll->status = 'paid';
        } elseif ($totalPaid > 0) {
            $payroll->status = 'partially_paid';
        } else {
            $payroll->status = 'calculated';
        }

        $payroll->save();

        return $payroll;
    }

    /**
     * Calculate payroll for all active staff members for a given month and year.
     */
    public function calculateAll(int $month, int $year): int
    {
        $barbers = Barber::where('status', 'active')->get();
        $count = 0;

        foreach ($barbers as $barber) {
            $this->calculateForBarber($barber, $month, $year);
            $count++;
        }

        return $count;
    }

    /**
     * Record an advance on salary for an employee.
     */
    public function addAdvance(int $barberId, int $month, int $year, float $amount, ?string $notes = null): Payroll
    {
        $barber = Barber::findOrFail($barberId);
        $payroll = Payroll::firstOrCreate(
            ['barberId' => $barberId, 'month' => $month, 'year' => $year],
            [
                'fixedSalary' => $barber->fixedSalary ?? 0,
                'commissions' => 0,
                'bonus' => 0,
                'advances' => 0,
                'deductions' => 0,
                'netSalary' => $barber->fixedSalary ?? 0,
                'status' => 'draft',
            ]
        );

        $payroll->advances = (float) $payroll->advances + $amount;
        $payroll->netSalary = max(0, ($payroll->fixedSalary + $payroll->commissions + $payroll->bonus) - ($payroll->advances + $payroll->deductions));
        $payroll->save();

        // If open cash register exists, record transaction
        $openRegister = CashRegister::where('status', 'open')->first();
        if ($openRegister) {
            CashTransaction::create([
                'cashRegisterId' => $openRegister->id,
                'type' => 'expense',
                'amount' => $amount,
                'description' => "Avance sur salaire pour {$barber->firstName} {$barber->lastName}".($notes ? " ({$notes})" : ''),
                'referenceId' => "advance_payroll_{$payroll->id}",
                'createdBy' => auth()->id(),
            ]);
        }

        return $payroll;
    }

    /**
     * Record a salary payment.
     */
    public function recordPayment(Payroll $payroll, float $amount, string $method = 'cash', ?string $notes = null): SalaryPayment
    {
        $payment = SalaryPayment::create([
            'payrollId' => $payroll->id,
            'barberId' => $payroll->barberId,
            'amount' => $amount,
            'date' => now(),
            'method' => $method,
            'notes' => $notes,
        ]);

        $totalPaid = (float) $payroll->salaryPayments()->sum('amount');
        if ($totalPaid >= (float) $payroll->netSalary) {
            $payroll->status = 'paid';
        } else {
            $payroll->status = 'partially_paid';
        }
        $payroll->save();

        // If cash and open register, record cash transaction
        if ($method === 'cash') {
            $openRegister = CashRegister::where('status', 'open')->first();
            if ($openRegister) {
                $barber = $payroll->barber;
                CashTransaction::create([
                    'cashRegisterId' => $openRegister->id,
                    'type' => 'expense',
                    'amount' => $amount,
                    'description' => 'Règlement salaire '.sprintf('%02d/%d', $payroll->month, $payroll->year)." pour {$barber?->firstName} {$barber?->lastName}".($notes ? " ({$notes})" : ''),
                    'referenceId' => "salary_payment_{$payment->id}",
                    'createdBy' => auth()->id(),
                ]);
            }
        }

        return $payment;
    }
}
