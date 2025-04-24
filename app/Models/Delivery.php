<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $table = 'deliveries';

    protected $fillable = [
        'driver_id',
        'driver',
        'cylinder',
        'size',
        'address',
        'customer',
        'date_assigned',
        'delivery_date',
        'delivery_time',
        'date_delivered',
        'time_delivered',
        'image_path',
    ];
}
