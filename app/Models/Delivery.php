<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


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

    /**
     * Relation to Cylinder model (preserves leading zeros).
     */
    public function cylinderRelation()
    {
        return $this->belongsTo(Cylinder::class, 'cylinder', 'id');
    }

    /**
     * Accessor for status based on multiple tables.
     */
    public function getStatusAttribute()
    {
        // Fetch cylinder record
        $cyl = DB::table('cylinders')->where('id', $this->cylinder)->first();
        $loc = $cyl->location ?? null;
        $lowerLoc = strtolower($loc ?: '');

        // 1. Unassigned
        if ($lowerLoc === 'unassigned') {
            return 'unassigned';
        }

        // Check if warehouse
        $isWarehouse = DB::table('warehouses')
            ->whereRaw('LOWER(name) = ?', [$lowerLoc])
            ->exists();
        $inPickup = DB::table('pickups')
            ->where('cylinder', $this->cylinder)
            ->exists();
        $inDelivery = DB::table('deliveries')
            ->where('cylinder', $this->cylinder)
            ->exists();

        // 2. At warehouse
        if ($isWarehouse && !$inPickup && !$inDelivery) {
            return 'at warehouse';
        }

        // 3. Awaiting customer pickup
        $pickup = DB::table('pickups')
            ->where('cylinder', $this->cylinder)
            ->first();
        if ($pickup && is_null($pickup->date_picked_up) && is_null($pickup->time_picked_up)) {
            return 'awaiting customer pickup';
        }

        // 4. With customer
        if ($cyl && $loc === $this->customer
            && (($pickup && $pickup->date_picked_up && $pickup->time_picked_up)
                || !is_null($this->approval))) {
            return 'with customer';
        }

        // 5. Awaiting driver pickup
        if (is_null($this->driver_pickup_date) && is_null($this->driver_pickup_time)
            && !$inPickup && !($pickup && !is_null($pickup->date_picked_up))) {
            return 'awaiting driver pickup';
        }

        // 6. With driver
        if ($this->driver_pickup_date && $this->driver_pickup_time && is_null($this->delivery_start)) {
            return 'with driver';
        }

        // 7. Being delivered
        if ($this->driver_pickup_date && $this->driver_pickup_time
            && $this->delivery_start && is_null($this->date_delivered) && is_null($this->time_delivered)) {
            return 'being delivered';
        }

        // 8. Delivery pending approval
        if ($this->driver_pickup_date && $this->driver_pickup_time
            && $this->delivery_start && $this->date_delivered && $this->time_delivered && $this->image_path
            && is_null($this->approval)) {
            return 'delivery pending approval';
        }

        // 9. Awaiting agent pickup
        $agent = DB::table('agent_cylinders_distribution')
            ->where('cylinder_id', $this->cylinder)
            ->first();
        if ($agent && is_null($agent->pick_up_date)) {
            return 'awaiting agent pickup';
        }

        // 10. With agent
        if ($agent && !is_null($agent->pick_up_date) && !$inPickup && !$inDelivery) {
            return 'with agent';
        }

        return 'unknown';
    }
}
