<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'scanned_by',
        'order_code',
        'is_valid',
        'reason',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_code', 'order_code');
    }
}
