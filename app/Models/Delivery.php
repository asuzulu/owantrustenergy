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
        $cylId = $this->id;

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

        // Ensure the cylinder ID is a 9-digit zero-padded string
        $paddedCylinderId = str_pad($this->id, 9, '0', STR_PAD_LEFT);

        // 3. Awaiting customer pickup
        $pickup = DB::table('pickups')
            ->where('cylinder', $paddedCylinderId)
            ->whereNull('date_picked_up')
            ->whereNull('time_picked_up')
            ->first();

        if ($pickup) {
            return 'Awaiting customer pickup';
        }

        // 4) With customer
        // ─────────────────────────────────────────────────────────────────────
        // First, check if “this location” matches a Customer’s full name (case‐insensitive).
        $customerNameMatch = DB::table('users')
            ->where('position', 'Customer')
            ->whereRaw('LOWER(first_name || " " || last_name) = ?', [$loc])
            ->exists();

        // Check if there is any approved delivery for this cylinder ID
        $hasCustomerDeliveryApproval = DB::table('deliveries')
            ->where('cylinder', $cylId)
            ->whereNotNull('approval')
            ->exists();

        // Now fetch the latest pickup row (by auto‐increment ID) for this padded cylinder ID
        $latestPickup = DB::table('pickups')
            ->where('cylinder', $paddedCylinderId)
            ->latest('id')
            ->first();

        /*
     * We enter “With customer” if:
     *   (1) The location string matches a Customer’s name, AND
     *   either
     *       (a) There is a latestPickup whose date_picked_up and time_picked_up are both NOT NULL, OR
     *       (b) There is an approved delivery for this cylinder (customer has already approved).
     */
        if (
            $customerNameMatch
            && (
                ($latestPickup
                    && ! is_null($latestPickup->date_picked_up)
                    && ! is_null($latestPickup->time_picked_up)
                )
                || $hasCustomerDeliveryApproval
            )
        ) {
            return 'With customer';
        }

        // 5. Awaiting driver pickup
        if (
            is_null($this->driver_pickup_date) && is_null($this->driver_pickup_time)
            && !$inPickup && !($pickup && !is_null($pickup->date_picked_up))
        ) {
            return 'awaiting driver pickup';
        }

        // 6. With driver
        if ($this->driver_pickup_date && $this->driver_pickup_time && is_null($this->delivery_start)) {
            return 'with driver';
        }

        // 7. Being delivered
        if (
            $this->driver_pickup_date && $this->driver_pickup_time
            && $this->delivery_start && is_null($this->date_delivered) && is_null($this->time_delivered)
        ) {
            return 'being delivered';
        }

        // 8. Delivery pending approval
        if (
            $this->driver_pickup_date && $this->driver_pickup_time
            && $this->delivery_start && $this->date_delivered && $this->time_delivered && $this->image_path
            && is_null($this->approval)
        ) {
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
