<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SearchAutoComplete extends Model
{
    /**
     * Search customers by name (first_name or last_name contains query).
     *
     * @param string|null $query
     * @return \Illuminate\Support\Collection
     */
    public static function searchCustomers(?string $query)
    {
        if (!$query) {
            return collect();
        }
        // Assuming customers are in users table with position = 'Customer'
        return User::select(['id', 'first_name', 'last_name'])
            ->where('position', 'Customer')
            ->where(function($q) use ($query) {
                $like = "%{$query}%";
                $q->where('first_name', 'like', $like)
                  ->orWhere('last_name', 'like', $like);
            })
            ->limit(10)
            ->get();
    }

    /**
     * Search drivers by name (first_name or last_name contains query).
     *
     * @param string|null $query
     * @return \Illuminate\Support\Collection
     */
    public static function searchDrivers(?string $query)
    {
        if (!$query) {
            return collect();
        }
        // Assuming drivers are in users table with position = 'Driver'
        return User::select(['id', 'first_name', 'last_name'])
            ->where('position', 'Driver')
            ->where(function($q) use ($query) {
                $like = "%{$query}%";
                $q->where('first_name', 'like', $like)
                  ->orWhere('last_name', 'like', $like);
            })
            ->limit(10)
            ->get();
    }
}
