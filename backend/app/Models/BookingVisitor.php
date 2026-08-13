<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingVisitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'ticket_category_id',
        'name',
        'gender',
        'age',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function ticketCategory()
    {
        return $this->belongsTo(TicketCategory::class);
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }
}
