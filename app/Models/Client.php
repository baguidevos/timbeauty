<?php

namespace App\Models;

use App\Events\ClientCreated;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'firstName',
        'lastName',
        'phone',
        'whatsapp',
        'email',
        'gender',
        'birthDate',
        'address',
        'notes',
        'firstVisitDate',
        'lastVisitDate',
        'totalVisits',
        'totalSpent',
        'isLoyal',
        'loyaltyPoints',
        'loyaltyTierId',
    ];

    protected function casts(): array
    {
        return [
            'birthDate' => 'date',
            'firstVisitDate' => 'date',
            'lastVisitDate' => 'date',
            'totalVisits' => 'integer',
            'totalSpent' => 'decimal:0',
            'isLoyal' => 'boolean',
            'loyaltyPoints' => 'integer',
        ];
    }

    public function loyaltyTier()
    {
        return $this->belongsTo(LoyaltyTier::class, 'loyaltyTierId');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'clientId');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'clientId');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'clientId');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'clientId');
    }

    public function loyaltyPointTransactions()
    {
        return $this->hasMany(LoyaltyPointTransaction::class, 'clientId');
    }

    public function promotionUsages()
    {
        return $this->hasMany(PromotionUsage::class, 'clientId');
    }

    public function appointmentPhotos()
    {
        return $this->hasMany(AppointmentPhoto::class, 'clientId');
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim("{$this->firstName} {$this->lastName}"),
        );
    }

    public function getFullName(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }

    public static function generateClientCode(?string $firstName, ?string $phone, int|string $id): string
    {
        $cleanLetters = preg_replace('/[^A-Za-z]/', '', Str::ascii((string) $firstName));
        $letters = empty($cleanLetters) ? 'CL' : Str::upper(substr(str_pad($cleanLetters, 2, 'X'), 0, 2));

        $digits = preg_replace('/\D/', '', (string) $phone);
        $lastFourDigits = str_pad(substr($digits, -4), 4, '0', STR_PAD_LEFT);

        return "{$letters}{$lastFourDigits}-{$id}";
    }

    public function getClientCodeAttribute(): ?string
    {
        return $this->code;
    }

    protected static function booted(): void
    {
        static::creating(function (Client $client): void {
            if (! $client->firstVisitDate) {
                $client->firstVisitDate = now()->toDateString();
            }
        });

        static::created(function (Client $client): void {
            if (empty($client->code)) {
                $client->code = static::generateClientCode(
                    firstName: $client->firstName,
                    phone: $client->phone ?: $client->whatsapp,
                    id: $client->id,
                );
                $client->saveQuietly();
            }

            ClientCreated::dispatch($client);
        });

        static::saving(function (Client $client): void {
            if ($client->exists && empty($client->code) && $client->id) {
                $client->code = static::generateClientCode(
                    firstName: $client->firstName,
                    phone: $client->phone ?: $client->whatsapp,
                    id: $client->id,
                );
            }
        });
    }

    public function getActivityDescription(string $action): string
    {
        $name = $this->getFullName();
        $code = $this->code ? " [{$this->code}]" : '';

        return match ($action) {
            'create' => "Nouveau client enregistré : {$name}{$code}",
            'update' => "Mise à jour du profil client : {$name}{$code}",
            'delete' => "Suppression du client : {$name}{$code}",
            default => "Action {$action} sur le client {$name}{$code}",
        };
    }
}
