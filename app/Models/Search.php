<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Search extends Model
{
    /**
     * Search for customers by first name or last name.
     *
     * @param string $query
     * @return \Illuminate\Support\Collection
     */
    public static function searchCustomers($query)
    {
        return self::searchUsersByPosition($query, 'Customer');
    }

    /**
     * Search for drivers by first name or last name.
     *
     * @param string $query
     * @return \Illuminate\Support\Collection
     */
    public static function searchDrivers($query)
    {
        return self::searchUsersByPosition($query, 'Driver');
    }

    /**
     * Search for users by first name or last name based on position.
     *
     * @param string $query
     * @param string $position
     * @return \Illuminate\Support\Collection
     */
    public static function searchUsersByPosition($query, $position)
    {
        return User::select('id', 'first_name', 'last_name', 'profile_image')
            ->where('position', $position)
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ["%" . strtolower($query) . "%"])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', ["%" . strtolower($query) . "%"])
                    ->orWhereRaw('LOWER(first_name || " " || last_name) LIKE ?', ["%" . strtolower($query) . "%"])
                    ->orWhereRaw('LOWER(last_name || " " || first_name) LIKE ?', ["%" . strtolower($query) . "%"]);
            })
            ->limit(15)
            ->get();
    }
}
