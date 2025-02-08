<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Statistics;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        $statistics = new Statistics();

        $data = [
            'cylinders_assigned' => $statistics->getCylindersAssigned(),
            'cylinders_assigned_chart' => $statistics->getCylindersAssignedChart(),
            'cylinders_in_warehouses' => $statistics->getCylindersInWarehouses(),
            'total_cylinders' => $statistics->getTotalCylinders(),
            'total_customers' => $statistics->getTotalCustomers(),
            'customers_last_month' => $statistics->getCustomersLastMonth(),
            'customers_last_week' => $statistics->getCustomersLastWeek(),
            'customers_last_year' => $statistics->getCustomersLastYear(),
            'total_employees' => $statistics->getTotalEmployees(),
            'customer_registration_chart' => $statistics->getCustomerRegistrationChart(),
            'total_warehouses' => $statistics->getTotalWarehouses(),
        ];

        return view('management.statistics', $data);
        
        // Fetch the cylinders assigned per month (Last 12 Months)
        $cylindersAssignedChart = DB::table('cylinders')
            ->whereNotNull('user_id')
            ->where('updated_at', '>=', now()->subMonths(12))
            ->selectRaw("strftime('%Y-%m', updated_at) as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fetch the customer registrations per month (Last 12 Months)
        $customerRegistrationsChart = DB::table('users')
            ->where('position', 'customer')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('management.statistics', compact('cylindersAssignedChart', 'customerRegistrationsChart'));
    }
}
