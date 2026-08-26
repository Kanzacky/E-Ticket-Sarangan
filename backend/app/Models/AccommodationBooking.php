<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationBooking extends Model
{
    protected $fillable = [
        'booking_code',
        'user_id',
        'accommodation_id',
        'check_in',
        'check_out',
        'rooms',
        'guests',
        'total_price',
        'guest_name',
        'guest_phone',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'rooms' => 'integer',
            'guests' => 'integer',
            'total_price' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }
}
