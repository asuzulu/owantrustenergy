<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'phone_number', 'bvn', 'nin', 'gender', 'street', 'city', 'state_id', 'email', 'password',
    ];

    // Relationship with the State model
    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
