<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'action',
        'entity',
        'entityId',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'entityId' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public static function log(string $action, ?string $entity = null, ?int $entityId = null, ?string $details = null, ?int $userId = null): self
    {
        return static::create([
            'userId' => $userId,
            'action' => $action,
            'entity' => $entity,
            'entityId' => $entityId,
            'details' => $details,
        ]);
    }
}
