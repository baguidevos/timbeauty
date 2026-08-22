<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'saleId',
        'clientId',
        'amount',
        'method',
        'reference',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:0',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'saleId');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function getActivityDescription(string $action): string
    {
        $amount = number_format((float) $this->amount, 0, ',', ' ');

        return match ($action) {
            'create' => "Règlement reçu de {$amount} FCFA par {$this->method} (Vente #{$this->saleId})",
            'update' => "Mise à jour du paiement #{$this->id} ({$amount} FCFA) - Statut : {$this->status}",
            'delete' => "Suppression du paiement #{$this->id}",
            default => "Action {$action} sur le paiement #{$this->id}",
        };
    }
}
