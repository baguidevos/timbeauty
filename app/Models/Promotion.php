<?php

namespace App\Models;

use App\Helpers\FormatHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'value',
        'startDate',
        'endDate',
        'minVisits',
        'forLoyalOnly',
        'maxUsages',
        'currentUsages',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:0',
            'startDate' => 'date',
            'endDate' => 'date',
            'minVisits' => 'integer',
            'forLoyalOnly' => 'boolean',
            'maxUsages' => 'integer',
            'currentUsages' => 'integer',
        ];
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'promotion_services', 'promotionId', 'serviceId');
    }

    public function promotionServices()
    {
        return $this->hasMany(PromotionService::class, 'promotionId');
    }

    public function usages()
    {
        return $this->hasMany(PromotionUsage::class, 'promotionId');
    }

    public function getDynamicStatusAttribute(): string
    {
        if ($this->status === 'inactive') {
            return 'inactive';
        }

        if ($this->maxUsages && $this->currentUsages >= $this->maxUsages) {
            return 'exhausted';
        }

        $today = Carbon::today();

        if ($this->startDate && $today->lt(Carbon::parse($this->startDate))) {
            return 'scheduled';
        }

        if ($this->endDate && $today->gt(Carbon::parse($this->endDate))) {
            return 'expired';
        }

        return 'active';
    }

    public function getDynamicStatusLabelAttribute(): string
    {
        return match ($this->dynamic_status) {
            'active' => 'Active',
            'scheduled' => 'Planifiée',
            'expired' => 'Expirée',
            'exhausted' => 'Épuisée',
            'inactive' => 'Désactivée',
            default => $this->status,
        };
    }

    public function getDynamicStatusColorAttribute(): string
    {
        return match ($this->dynamic_status) {
            'active' => 'success',
            'scheduled' => 'info',
            'expired' => 'danger',
            'exhausted' => 'warning',
            'inactive' => 'gray',
            default => 'gray',
        };
    }

    public function getFormattedValueAttribute(): string
    {
        return match ($this->type) {
            'percentage' => "-{$this->value}%",
            'fixed' => '-'.FormatHelper::formatFCFA($this->value),
            'free_service' => 'Prestation offerte',
            default => (string) $this->value,
        };
    }

    public function getUsageRatioAttribute(): string
    {
        $current = $this->currentUsages ?? 0;
        $max = $this->maxUsages ? $this->maxUsages : '∞';

        return "{$current} / {$max}";
    }

    public function isActive(): bool
    {
        return $this->dynamic_status === 'active';
    }
}
