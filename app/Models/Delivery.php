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
        'customer',
        'address',
        'date_assigned',
        'delivery_date',
        'delivery_time',
        'date_delivered',
        'time_delivered',
        'image_path',
    ];

    protected $casts = [
        'date_assigned'  => 'date',
        'delivery_date'  => 'date',
        'date_delivered' => 'date',
        'delivery_time'  => 'datetime:H:i:s',
        'time_delivered' => 'datetime:H:i:s',
    ];

    public function driverUser()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
