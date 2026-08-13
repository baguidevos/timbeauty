<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'categoryId');
    }
}
