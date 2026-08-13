<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
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

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}
