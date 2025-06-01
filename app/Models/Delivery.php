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
        'disapproval_reason',
        'disapproval_customer_response',
        'disapproval_driver_response',
        'was_cylinder_delivered',
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
            return 'Unassigned';
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

        // Latest pickup stub
        $pk = DB::table('pickups')
            ->where('cylinder', $cylId)
            ->latest('id')
            ->first();

        // 2. At warehouse
        if ($isWarehouse && !$inPickup && !$inDelivery) {
            return 'At warehouse';
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
        $customerNameMatch = DB::table('users')
            ->where('position', 'Customer')
            ->whereRaw('LOWER(first_name || " " || last_name) = ?', [$loc])
            ->exists();

        $hasCustomerDeliveryApproval = DB::table('deliveries')
            ->where('cylinder', $cylId)
            ->whereNotNull('approval')
            ->exists();

        if (
            $customerNameMatch
            && (
                ($pk && $pk->date_picked_up && $pk->time_picked_up)
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
            return 'Awaiting driver pickup';
        }

        // 6. With driver
        if ($this->driver_pickup_date && $this->driver_pickup_time && is_null($this->delivery_start)) {
            return 'With driver';
        }

        // 7. Being delivered
        if (
            $this->driver_pickup_date && $this->driver_pickup_time
            && $this->delivery_start && is_null($this->date_delivered) && is_null($this->time_delivered)
        ) {
            return 'Being delivered to customer';
        }

        // 8. Delivery pending approval
        if (
            $this->driver_pickup_date && $this->driver_pickup_time
            && $this->delivery_start && $this->date_delivered && $this->time_delivered && $this->image_path
            && is_null($this->approval)
        ) {
            return 'Delivery pending approval';
        }

        // 9. Awaiting agent pickup
        $agent = DB::table('agent_cylinders_distribution')
            ->where('cylinder_id', $this->cylinder)
            ->first();
        if ($agent && is_null($agent->pick_up_date)) {
            return 'Awaiting agent pickup';
        }

        // 10. With agent
        if ($agent && !is_null($agent->pick_up_date) && !$inPickup && !$inDelivery) {
            return 'With agent';
        }

        return 'Unknown';
    }
}
