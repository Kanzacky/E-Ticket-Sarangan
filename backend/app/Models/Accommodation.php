<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accommodation extends Model
{
    protected $fillable = [
        'name',
        'description',
        'address',
        'phone',
        'image_url',
        'price_per_night',
        'total_rooms',
        'available_rooms',
        'rating',
        'facilities',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_night' => 'integer',
            'total_rooms' => 'integer',
            'available_rooms' => 'integer',
            'rating' => 'decimal:1',
            'facilities' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(AccommodationBooking::class);
    }
}
