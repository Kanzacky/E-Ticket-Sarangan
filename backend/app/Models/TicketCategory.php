<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'min_age',
        'max_age',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
