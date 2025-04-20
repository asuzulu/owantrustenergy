<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'customer_id',
        'cylinder_size',
        'weight',
        'order_type',
        'retrieval',
        'date_picked_up',
        'time_picked_up',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
