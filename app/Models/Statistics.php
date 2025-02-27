<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Statistics extends Model
{
    public static function getCylindersAssignedCount()
    {
        return DB::table('cylinders')
            ->join('users', DB::raw("cylinders.location"), "=", DB::raw("users.first_name || ' ' || users.last_name"))
            ->count();
    }

    public static function getCylindersInWarehouses()
    {
        return DB::table('cylinders')
            ->join('warehouses', 'cylinders.location', '=', 'warehouses.name')
            ->count();
    }

    public static function getTotalCylinders()
    {
        return DB::table('cylinders')->count();
    }

    public static function getTotalCustomers()
    {
        return DB::table('users')->where('position', 'customer')->count();
    }

    public static function getNewCustomersLastMonth()
    {
        return DB::table('users')
        ->where('position', 'Customer')
        ->where('created_at', '>=', now()->subMonth())
            ->count();
    }

    public static function getNewCustomersLastWeek()
    {
        return DB::table('users')
        ->where('position', 'Customer')
        ->where('created_at', '>=', now()->subWeek())
            ->count();
    }

    public static function getNewCustomersLastYear()
    {
        return DB::table('users')
        ->where('position', 'Customer')
        ->where('created_at', '>=', now()->subYear())
            ->count();
    }

    public static function getTotalCustomersAllTime()
    {
        return DB::table('users')->where('position', 'customer')->count();
    }

    public static function getTotalWarehouses()
    {
        return DB::table('warehouses')->count();
    }

    public static function getTotalEmployees()
    {
        return DB::table('users')->where('position', 'Employee')->count();
    }

    public static function getCylindersAssignedChart()
    {
        return DB::table('cylinders')
            ->whereNotNull('user_id')
            ->where('updated_at', '>=', now()->subMonths(12))
            ->selectRaw("strftime('%Y-%m', updated_at) as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public static function getCustomerRegistrationsChart()
    {
        return DB::table('users')
        ->where('position', 'Customer')
        ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
