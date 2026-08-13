<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

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
}
