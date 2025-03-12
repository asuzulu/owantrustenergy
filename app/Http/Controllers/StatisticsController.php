<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Statistics;
use Illuminate\Support\Facades\DB;
use App\Models\Cylinder;
use App\Models\User;
use App\Models\Warehouse;

class StatisticsController extends Controller
{
    public function index()
    {
        $databaseConnection = DB::getDriverName();
        $dateFunction = $databaseConnection === 'sqlite' ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";

        // 1. Cylinders assigned to users
        $cylinders_assigned = Cylinder::whereIn('location', function ($query) {
            $query->selectRaw("TRIM(first_name || ' ' || last_name) as full_name")->from('users');
        })->count();

        // 2. Cylinders in warehouses
        $cylinders_in_warehouses = Cylinder::whereIn('location', Warehouse::pluck('name'))->count();

        // 3. Total cylinders
        $total_cylinders = Cylinder::count();

        // 4. Total customers
        $total_customers = User::where('position', 'Customer')->count();

        // 5. Customers registered in the last year
        $customers_last_year = User::where('position', 'Customer')
            ->where('created_at', '>=', now()->subYear())
            ->count();

        // 6. Customers registered in the last 30 days
        $customers_last_month = User::where('position', 'Customer')
            ->whereDate('created_at', '>=', now()->subDays(30)->toDateString())
            ->count();

        // 7. Customers registered in the last 7 days
        $customers_last_week = User::where('position', 'Customer')
            ->whereDate('created_at', '>=', now()->subDays(7)->toDateString())
            ->count();

        // 8. Total customers since the beginning
        $customers_since_beginning = User::where('position', 'Customer')->count();

        // 9. Total employees
        $total_employees = User::where('position', 'Employee')->count();

        // 10. Total warehouses
        $total_warehouses = Warehouse::count();

        // 11. Cylinders assigned per month (last 12 months)
        $cylindersAssignedChart = Cylinder::selectRaw("$dateFunction as month, COUNT(*) as count")
            ->whereNotNull('location')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 12. Customers registered per month (last 12 months)
        $customerRegistrationsChart = User::selectRaw("$dateFunction as month, COUNT(*) as count")
            ->where('position', 'Customer')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('management.statistics', compact(
            'cylinders_assigned',
            'cylinders_in_warehouses',
            'total_cylinders',
            'total_customers',
            'customers_last_month',
            'customers_last_week',
            'customers_last_year',
            'total_employees',
            'total_warehouses',
            'cylindersAssignedChart',
            'customerRegistrationsChart'
        ));
    }

    public function getStatisticsData()
    {
        try {
            $databaseConnection = DB::getDriverName();
            $dateFunction = $databaseConnection === 'sqlite' ? "strftime('%Y-%m', updated_at)" : "DATE_FORMAT(updated_at, '%Y-%m')";

            $cylindersAssignedChart = DB::table('cylinders')
                ->whereNotNull('user_id')
                ->where('updated_at', '>=', now()->subMonths(12))
                ->selectRaw("$dateFunction as month, COUNT(*) as count")
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $customerRegistrationsChart = DB::table('users')
                ->where('position', 'Customer')
                ->where('created_at', '>=', now()->subMonths(12))
                ->selectRaw("$dateFunction as month, COUNT(*) as count")
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return response()->json([
                'cylindersAssignedChart' => $cylindersAssignedChart,
                'customerRegistrationsChart' => $customerRegistrationsChart
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
