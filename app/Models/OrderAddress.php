<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id',
        'full_name',
        'phone',
        'city',
        'area',
        'street',
        'building',
        'floor',
        'apartment',
        'notes',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
