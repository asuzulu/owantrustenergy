<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'gender',
        'street',
        'city',
        'state',
        'bvn',
        'nin',
        'email',
        'password',
        'profile_image',
        'dob',
        'position',
    ];

    protected $casts = [
        'profile_image' => 'string',
        'dob' => 'date',
    ];

    public function isManager()
    {
        return $this->position === 'Manager';
    }

    public function isEmployee()
    {
        return $this->position === 'Employee';
    }

    public function isAgent()
    {
        return $this->position === 'Agent';
    }

    public function isDriver()
    {
        return $this->position === 'Driver';
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function cylinderDistributions()
    {
        return $this->hasMany(\Illuminate\Database\Eloquent\Model::class, 'agent_id', 'id')
            ->setTable('agent_cylinders_distribution');
    }

    protected static function booted()
    {
        static::deleting(function ($user) {
            if ($user->position === 'Agent') {
                // Get all distributed cylinders
                $distributions = DB::table('agent_cylinders_distribution')
                    ->where('agent_id', $user->id)
                    ->get();

                // Group by size for logging
                $grouped = $distributions->groupBy('cylinder_size');

                $log = "{$user->first_name} {$user->last_name} was deleted from the system at " . now()->toDateTimeString() . PHP_EOL;
                $log .= "Total Cylinders in Possession: " . $distributions->count() . PHP_EOL;

                $sizeLabels = [
                    '3kg' => 'Small (3kg)',
                    '5kg' => 'Medium (5kg)',
                    '12kg' => 'Large (12kg)',
                    '25kg' => 'XL (25kg)',
                ];

                foreach ($grouped as $size => $cylinders) {
                    $label = $sizeLabels[$size] ?? $size;
                    $ids = $cylinders->pluck('cylinder_id')->implode(', ');
                    $log .= "{$label}: {$ids}" . PHP_EOL;
                }

                $log .= PHP_EOL . PHP_EOL;

                // Write to custom log file
                Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/agent_deletion.log'),
                ])->info($log);

                // ✅ Set all affected cylinders' location to 'Unassigned'
                $cylinderIds = $distributions->pluck('cylinder_id')->toArray();
                DB::table('cylinders')
                    ->whereIn('id', $cylinderIds)
                    ->update(['location' => 'Unassigned']);

                // Delete distributions
                DB::table('agent_cylinders_distribution')
                    ->where('agent_id', $user->id)
                    ->delete();
            }
        });
    }

    // MUTATORS

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucwords(strtolower($value));
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucwords(strtolower($value));
    }

    public function setStreetAttribute($value)
    {
        $this->attributes['street'] = ucwords(strtolower($value));
    }

    public function setCityAttribute($value)
    {
        $this->attributes['city'] = ucwords(strtolower($value));
    }

    public function setStateAttribute($value)
    {
        $this->attributes['state'] = ucwords(strtolower($value));
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }
}
