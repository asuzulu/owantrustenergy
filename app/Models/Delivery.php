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
        'customer_id',
        'customer',
        'address',
        'date_assigned',
        'delivery_date',
        'delivery_time',
        'delivery_start',
        'date_delivered',
        'time_delivered',
        'driver_pickup_date',
        'driver_pickup_time',
        'image_path',
        'approval',
        'passcode',
    ];

    protected $casts = [
        'date_assigned'      => 'date',
        'delivery_date'      => 'date',
        'delivery_start'     => 'datetime',
        'date_delivered'     => 'date',
        'driver_pickup_date' => 'date',
        'delivery_time'      => 'datetime:H:i:s',
        'time_delivered'     => 'datetime:H:i:s',
        'driver_pickup_time' => 'datetime:H:i:s',
    ];

    /**
     * Get the driver (user) who is assigned.
     */
    public function driverUser()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the customer (user) who is receiving.
     */
    public function customerUser()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
