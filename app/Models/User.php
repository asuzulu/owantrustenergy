<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

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
}
