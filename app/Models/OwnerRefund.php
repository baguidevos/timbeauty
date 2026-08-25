<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerRefund extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ownerAdvanceId',
        'cashRegisterId',
        'amount',
        'paymentMethod',
        'notes',
        'createdBy',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:0',
        ];
    }

    public function ownerAdvance()
    {
        return $this->belongsTo(OwnerAdvance::class, 'ownerAdvanceId');
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'cashRegisterId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    protected static function booted(): void
    {
        static::created(function (OwnerRefund $refund): void {
            $refund->ownerAdvance?->updateRefundProgress();

            if ($refund->paymentMethod === 'cash' && $refund->cashRegisterId) {
                CashTransaction::create([
                    'cashRegisterId' => $refund->cashRegisterId,
                    'type' => 'owner_refund',
                    'amount' => $refund->amount,
                    'description' => 'Remboursement avance propriétaire #'.$refund->ownerAdvanceId.($refund->notes ? ' ('.$refund->notes.')' : ''),
                    'referenceId' => (string) $refund->id,
                    'createdBy' => $refund->createdBy ?? auth()->id(),
                ]);
            }
        });

        static::deleted(function (OwnerRefund $refund): void {
            $refund->ownerAdvance?->updateRefundProgress();
        });
    }

    public function getActivityDescription(string $action): string
    {
        $amount = number_format((float) $this->amount, 0, ',', ' ');
        $ownerName = $this->ownerAdvance?->user?->name ?? 'Propriétaire';

        return match ($action) {
            'create' => "Remboursement d'avance de {$amount} FCFA versé à {$ownerName}",
            'update' => "Mise à jour du remboursement #{$this->id}",
            'delete' => "Suppression du remboursement #{$this->id}",
            default => "Action {$action} sur le remboursement #{$this->id}",
        };
    }
}
