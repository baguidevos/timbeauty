<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientId',
        'type',
        'channel',
        'message',
        'read',
        'sent',
        'sentAt',
    ];

    protected function casts(): array
    {
        return [
            'read' => 'boolean',
            'sent' => 'boolean',
            'sentAt' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function markAsRead(): void
    {
        $this->update(['read' => true]);
    }

    public function markAsSent(): void
    {
        $this->update(['sent' => true, 'sentAt' => now()]);
    }
}
