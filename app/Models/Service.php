<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'categoryId',
        'commissionRate',
        'status',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:0',
            'duration' => 'integer',
            'commissionRate' => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'categoryId');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'serviceId');
    }

    public function promotionServices()
    {
        return $this->hasMany(PromotionService::class, 'serviceId');
    }

    public function loyaltyRules()
    {
        return $this->hasMany(LoyaltyRule::class, 'serviceId');
    }

    public function getActivityDescription(string $action): string
    {
        $price = number_format((float) $this->price, 0, ',', ' ');

        return match ($action) {
            'create' => "Nouvelle prestation ajoutée au catalogue : {$this->name} ({$price} FCFA)",
            'update' => "Mise à jour de la prestation : {$this->name} ({$price} FCFA)",
            'delete' => "Suppression de la prestation : {$this->name}",
            default => "Action {$action} sur la prestation {$this->name}",
        };
    }
}
