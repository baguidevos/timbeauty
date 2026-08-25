<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerAdvance extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'cashRegisterId',
        'userId',
        'amount',
        'refundedAmount',
        'status',
        'reason',
        'notes',
        'createdBy',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:0',
            'refundedAmount' => 'decimal:0',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'cashRegisterId');
    }

    public function refunds()
    {
        return $this->hasMany(OwnerRefund::class, 'ownerAdvanceId');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->refundedAmount);
    }

    public function isFullyRefunded(): bool
    {
        return $this->remaining_amount <= 0;
    }

    public function updateRefundProgress(): void
    {
        $totalRefunded = (float) $this->refunds()->sum('amount');
        $this->refundedAmount = $totalRefunded;

        if ($totalRefunded >= (float) $this->amount) {
            $this->status = 'refunded';
        } elseif ($totalRefunded > 0) {
            $this->status = 'partially_refunded';
        } else {
            $this->status = 'pending';
        }

        $this->save();
    }

    public function getActivityDescription(string $action): string
    {
        $amount = number_format((float) $this->amount, 0, ',', ' ');
        $ownerName = $this->user?->name ?? 'Propriétaire';

        return match ($action) {
            'create' => "Nouvel apport / avance de fonds de {$amount} FCFA par {$ownerName}",
            'update' => "Mise à jour de l'avance #{$this->id} ({$amount} FCFA)",
            'delete' => "Suppression de l'avance #{$this->id}",
            default => "Action {$action} sur l'avance #{$this->id}",
        };
    }
}
