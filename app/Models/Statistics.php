<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Statistics extends Model
{
    public function getCylindersAssigned()
    {
        return DB::table('cylinders')->whereNotNull('user_id')->count() ?: 'N/A';
    }

    public function getCylindersAssignedChart()
    {
        return DB::table('cylinders')
            ->select(DB::raw("strftime('%Y-%m', updated_at) as month, COUNT(*) as count"))
            ->whereNotNull('user_id')
            ->where('assigned_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->get();
    }

    public function getCylindersInWarehouses()
    {
        return DB::table('cylinders')->whereNotNull('warehouse_id')->count() ?: 'N/A';
    }

    public function getTotalCylinders()
    {
        return DB::table('cylinders')->count() ?: 'N/A';
    }

    public function getTotalCustomers()
    {
        return DB::table('users')->where('position', 'Customer')->count() ?: 'N/A';
    }

    public function getCustomersLastMonth()
    {
        return DB::table('users')
            ->where('position', 'Customer')
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->count() ?: 'N/A';
    }

    public function getCustomersLastWeek()
    {
        return DB::table('users')
            ->where('position', 'Customer')
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->count() ?: 'N/A';
    }

    public function getCustomersLastYear()
    {
        return DB::table('users')
            ->where('position', 'Customer')
            ->where('created_at', '>=', Carbon::now()->subYear())
            ->count() ?: 'N/A';
    }

    public function getTotalEmployees()
    {
        return DB::table('users')->where('position', 'Employee')->count() ?: 'N/A';
    }

    public function getCustomerRegistrationChart()
    {
        return DB::table('users')
            ->select(DB::raw("strftime('%Y-%m', created_at) as month, COUNT(*) as count"))
            ->where('position', 'Customer')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->get();
    }

    public function getTotalWarehouses()
    {
        return DB::table('warehouses')->count() ?: 'N/A';
    }
}
