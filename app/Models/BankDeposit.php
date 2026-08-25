<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDeposit extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'reference',
        'cashRegisterId',
        'amount',
        'bankName',
        'bankAccountNumber',
        'depositSlipNumber',
        'depositSlipPhoto',
        'depositedBy',
        'status',
        'depositDate',
        'notes',
        'createdBy',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:0',
            'depositDate' => 'datetime',
        ];
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'cashRegisterId');
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'depositedBy');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public static function generateReference(): string
    {
        $prefix = 'DEP-'.now()->format('Ym').'-';
        $latest = static::where('reference', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->reference, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }

        return $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::creating(function (BankDeposit $deposit): void {
            if (empty($deposit->reference)) {
                $deposit->reference = static::generateReference();
            }

            if (empty($deposit->depositDate)) {
                $deposit->depositDate = now();
            }
        });

        static::created(function (BankDeposit $deposit): void {
            if ($deposit->cashRegisterId) {
                CashTransaction::create([
                    'cashRegisterId' => $deposit->cashRegisterId,
                    'type' => 'bank_deposit',
                    'amount' => $deposit->amount,
                    'description' => 'Dépôt en banque ('.$deposit->bankName.') - Réf: '.$deposit->reference.($deposit->depositSlipNumber ? ' / Bordereau: '.$deposit->depositSlipNumber : ''),
                    'referenceId' => (string) $deposit->id,
                    'createdBy' => $deposit->createdBy ?? auth()->id(),
                ]);
            }
        });
    }

    public function getActivityDescription(string $action): string
    {
        $amount = number_format((float) $this->amount, 0, ',', ' ');

        return match ($action) {
            'create' => "Remise en banque de {$amount} FCFA ({$this->bankName}) - Réf: {$this->reference}",
            'update' => "Mise à jour de la remise en banque {$this->reference}",
            'delete' => "Suppression de la remise en banque {$this->reference}",
            default => "Action {$action} sur la remise en banque {$this->reference}",
        };
    }
}
