<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'openingAmount',
        'closingAmount',
        'status',
        'openedAt',
        'closedAt',
        'openedBy',
    ];

    protected function casts(): array
    {
        return [
            'openingAmount' => 'decimal:0',
            'closingAmount' => 'decimal:0',
            'openedAt' => 'datetime',
            'closedAt' => 'datetime',
        ];
    }

    public function opener()
    {
        return $this->belongsTo(User::class, 'openedBy');
    }

    public function transactions()
    {
        return $this->hasMany(CashTransaction::class, 'cashRegisterId');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'cashRegisterId');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'cashRegisterId');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function close(float $closingAmount): void
    {
        $this->update([
            'closingAmount' => $closingAmount,
            'status' => 'closed',
            'closedAt' => now(),
        ]);
    }

    public static function getOperationMode(): string
    {
        return Setting::get('cash_register_mode', 'auto_open'); // 'auto_open', 'strict', 'flexible'
    }

    public static function resolveForOperation(string $paymentMethod = 'cash'): ?self
    {
        $openRegister = static::where('status', 'open')->first();

        if ($openRegister) {
            return $openRegister;
        }

        // Si ce n'est pas un paiement en cash (ex: carte, virement), pas de contrainte de caisse physique
        if ($paymentMethod !== 'cash') {
            return null;
        }

        $mode = static::getOperationMode();

        if ($mode === 'strict') {
            throw ValidationException::withMessages([
                'paymentMethod' => 'Impossible d\'enregistrer une opération en espèces : aucune session de caisse n\'est ouverte. Veuillez d\'abord ouvrir la caisse du jour.',
            ]);
        }

        if ($mode === 'auto_open') {
            $lastClosed = static::where('status', 'closed')->latest('closedAt')->first();
            $openingAmount = $lastClosed ? (float) $lastClosed->closingAmount : 0;

            $newRegister = static::create([
                'openingAmount' => $openingAmount,
                'status' => 'open',
                'openedAt' => now(),
                'openedBy' => auth()->id(),
            ]);

            return $newRegister;
        }

        // Mode 'flexible' : on laisse l'opération s'enregistrer sans caisse immédiate
        return null;
    }

    public function attachOrphanedOperations(): void
    {
        // 1. Dépenses orphelines du jour
        $orphanedExpenses = Expense::whereNull('cashRegisterId')
            ->whereDate('created_at', now()->toDateString())
            ->get();

        foreach ($orphanedExpenses as $expense) {
            $expense->update(['cashRegisterId' => $this->id]);
            if ($expense->paymentMethod === 'cash') {
                CashTransaction::firstOrCreate(
                    [
                        'cashRegisterId' => $this->id,
                        'type' => 'expense',
                        'referenceId' => (string) $expense->id,
                    ],
                    [
                        'amount' => $expense->amount,
                        'description' => 'Dépense : '.($expense->category?->name ?? 'Générale').($expense->beneficiary ? ' — '.$expense->beneficiary : ''),
                        'createdBy' => $expense->createdBy ?? auth()->id(),
                    ]
                );
            }
        }

        // 2. Ventes orphelines du jour
        $orphanedSales = Sale::whereNull('cashRegisterId')
            ->whereDate('created_at', now()->toDateString())
            ->get();

        foreach ($orphanedSales as $sale) {
            $sale->update(['cashRegisterId' => $this->id]);
            if ($sale->paymentMethod === 'cash') {
                CashTransaction::firstOrCreate(
                    [
                        'cashRegisterId' => $this->id,
                        'type' => 'sale',
                        'referenceId' => (string) $sale->id,
                    ],
                    [
                        'amount' => $sale->total,
                        'description' => 'Encaissement Vente / Prestation #'.$sale->id,
                        'createdBy' => $sale->createdBy ?? auth()->id(),
                    ]
                );
            }
        }
    }

    protected static function booted(): void
    {
        static::created(function (CashRegister $register): void {
            if ($register->isOpen()) {
                $register->attachOrphanedOperations();
            }
        });
    }
}
