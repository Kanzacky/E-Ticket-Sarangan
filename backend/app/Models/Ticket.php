<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_visitor_id',
        'ticket_code',
        'qr_code_path',
        'status',
    ];

    public function visitor()
    {
        return $this->belongsTo(BookingVisitor::class, 'booking_visitor_id');
    }

    public function checkin()
    {
        return $this->hasOne(Checkin::class);
    }

    public function upgrades()
    {
        return $this->hasMany(TicketUpgrade::class);
    }
}
