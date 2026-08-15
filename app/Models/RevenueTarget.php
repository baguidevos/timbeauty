<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'barberId',
        'month',
        'year',
        'targetAmount',
        'createdBy',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'targetAmount' => 'decimal:0',
        ];
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function isShopTarget(): bool
    {
        return $this->type === 'shop';
    }

    public function isBarberTarget(): bool
    {
        return $this->type === 'barber';
    }

    /**
     * Compute actual revenue completed for this target period in real time.
     */
    public function getActualRevenueAttribute(): float
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $query = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($this->type === 'barber' && $this->barberId) {
            $query->where('barberId', $this->barberId);
        }

        return (float) $query->sum('total');
    }

    /**
     * Compute percentage achieved.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ((float) $this->targetAmount <= 0) {
            return 0;
        }

        return round(($this->actual_revenue / (float) $this->targetAmount) * 100, 1);
    }

    /**
     * Remaining revenue required to reach target.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->targetAmount - $this->actual_revenue);
    }

    /**
     * Color code for progress (emerald, amber, rose).
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->progress_percentage >= 80) {
            return 'emerald';
        }
        if ($this->progress_percentage >= 50) {
            return 'amber';
        }

        return 'rose';
    }

    /**
     * Status label for progress evaluation.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->progress_percentage >= 100) {
            return 'Objectif atteint 🎉';
        }
        if ($this->progress_percentage >= 80) {
            return 'Excellent';
        }
        if ($this->progress_percentage >= 50) {
            return 'En cours';
        }

        return 'En retard';
    }
}
