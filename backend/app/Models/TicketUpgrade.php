<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketUpgrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'old_ticket_category_id',
        'new_ticket_category_id',
        'additional_amount',
        'status',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function oldCategory()
    {
        return $this->belongsTo(TicketCategory::class, 'old_ticket_category_id');
    }

    public function newCategory()
    {
        return $this->belongsTo(TicketCategory::class, 'new_ticket_category_id');
    }
}
