<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
    use HasFactory;

    protected $table = 'pickups';

    protected $fillable = [
        'location',
        'cylinder',
        'size',
        'customer',
        'date_assigned',
        'pick_up_date',
        'date_picked_up',
        'time_picked_up',
    ];
}
