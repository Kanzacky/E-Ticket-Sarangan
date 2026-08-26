<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_code',
        'visit_date',
        'customer_name',
        'customer_email',
        'customer_phone',
        'total_quantity',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'date:Y-m-d',
        'total_quantity' => 'integer',
        'total_amount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
