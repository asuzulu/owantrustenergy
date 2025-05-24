<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cylinder extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id',
        'size',
        'location',
        'allocated_date',
        'user_id',
        'qr_code_path',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'allocated_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function delivery()
    {
        return $this
            ->hasOne(\App\Models\Delivery::class, 'cylinder', 'id')
            ->latestOfMany();
    }

    public function pickup()
    {
        return $this
            ->hasOne(\App\Models\Pickup::class, 'cylinder', 'id')
            ->latestOfMany();
    }

    public function getStatusAttribute()
    {
        $loc   = strtolower($this->location ?? '');
        $cylId = $this->id;

        // 1) Unassigned
        if ($loc === 'Unassigned') {
            return 'Unassigned';
        }

        // Existence flags
        $inPickup   = DB::table('pickups')->where('cylinder', $cylId)->exists();
        $inDelivery = DB::table('deliveries')->where('cylinder', $cylId)->exists();

        // 2) At warehouse
        $isWarehouse = DB::table('warehouses')
            ->whereRaw('LOWER(name) = ?', [$loc])
            ->exists();
        if ($isWarehouse && ! $inPickup && ! $inDelivery) {
            return 'At warehouse';
        }

        // Latest pickup stub
        $pk = DB::table('pickups')
            ->where('cylinder', $cylId)
            ->latest('id')
            ->first();

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

        // Latest delivery stub
        $dl = DB::table('deliveries')
            ->where('cylinder', $cylId)
            ->latest('id')
            ->first();

        if ($dl) {
            // 5) Waiting for driver pickup
            if (is_null($dl->driver_pickup_date) && is_null($dl->driver_pickup_time)) {
                return 'Waiting for driver pickup';
            }

            // 6) With driver
            if (
                $dl->driver_pickup_date
                && $dl->driver_pickup_time
                && is_null($dl->delivery_start)
            ) {
                return 'With driver';
            }

            // 7) Being delivered
            if (
                $dl->driver_pickup_date
                && $dl->driver_pickup_time
                && $dl->delivery_start
                && is_null($dl->date_delivered)
                && is_null($dl->time_delivered)
            ) {
                return 'Being delivered to customer';
            }

            // 8) Delivery pending approval
            if (
                $dl->driver_pickup_date
                && $dl->driver_pickup_time
                && $dl->delivery_start
                && $dl->date_delivered
                && $dl->time_delivered
                && $dl->image_path
                && is_null($dl->approval)
            ) {
                return 'Delivery pending approval';
            }
        }

        // Latest agent distribution stub
        $ag = DB::table('agent_cylinders_distribution')
            ->where('cylinder_id', $cylId)
            ->latest('id')
            ->first();

        if ($ag) {
            // 9) Waiting for agent pickup
            if (is_null($ag->pick_up_date)) {
                return 'Waiting for agent pickup';
            }
            // 10) With agent
            if ($ag->pick_up_date && ! $inPickup && ! $inDelivery) {
                return 'With agent';
            }
        }

        return 'Unknown';
    }
}
