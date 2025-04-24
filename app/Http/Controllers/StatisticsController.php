<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cylinder;
use App\Models\User;
use App\Models\Warehouse;

class StatisticsController extends Controller
{
    public function index()
    {
        $databaseConnection = DB::getDriverName();
        $dateFunction = $databaseConnection === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        // 1. Cylinders assigned to users
        $cylinders_assigned = Cylinder::whereIn('location', function ($query) {
            $query->selectRaw("TRIM(first_name || ' ' || last_name) as full_name")
                ->from('users');
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

        // ─── NEW: Customers created in the last 7 days (alias) ───────────────────────
        $new_customers_this_week = User::where('position', 'Customer')
            ->whereDate('created_at', '>=', now()->subDays(7)->toDateString())
            ->count();

        // 8. Total employees
        $total_employees = User::where('position', 'Employee')->count();

        // 9. Total warehouses
        $total_warehouses = Warehouse::count();
        // New Customers This Week
        $new_customers_this_week = User::where('position', 'Customer')
            ->whereDate('created_at', '>=', now()->subDays(7)->toDateString())
            ->count();

        // Unassigned Cylinders
        $unassigned_cylinders = Cylinder::where('location', 'Unassigned')->count();

        // New Employees This Month
        $new_employees_this_month = User::where('position', 'Employee')
            ->whereDate('created_at', '>=', now()->startOfMonth()->toDateString())
            ->count();


        // Prepare full‐name expression for matching
        $fullNameExpr = $databaseConnection === 'sqlite'
            ? "TRIM(first_name || ' ' || last_name)"
            : "TRIM(CONCAT(first_name, ' ', last_name))";

        // A. How many customers are assigned cylinders (distinct users)
        $customerNames = User::where('position', 'Customer')
            ->selectRaw("$fullNameExpr as full_name")
            ->pluck('full_name')
            ->toArray();

        $customers_assigned = DB::table('cylinders')
            ->whereIn('location', $customerNames)
            ->distinct()
            ->count('location');

        // B. How many customers aren’t assigned cylinders
        $customers_unassigned = $total_customers - $customers_assigned;

        // C. How many Customer Order requests are pending
        $customer_orders_pending = DB::table('orders')->count();

        // D. How many cylinders are distributed to agents
        $cylinders_distributed_agents = DB::table('agent_cylinders_distribution')->count();

        // E. How many agents have cylinders distributed to them
        $agents_with_distribution = DB::table('agent_cylinders_distribution')
            ->distinct()
            ->count('agent_id');

        // F. How many deliveries are pending
        $deliveries_pending = DB::table('deliveries')
            ->whereNull('date_delivered')
            ->count();

        // G. How many pickup orders are pending
        $pickup_orders_pending = DB::table('pickups')
            ->whereNull('date_picked_up')
            ->count();

        // H. Total drivers
        $total_drivers = User::where('position', 'Driver')->count();

        // I. How many drivers have deliveries to make
        $drivers_with_deliveries = DB::table('deliveries')
            ->distinct()
            ->count('driver_id');

        // Charts data (last 12 months)
        $cylindersAssignedChart = Cylinder::selectRaw("$dateFunction as month, COUNT(*) as count")
            ->whereNotNull('location')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

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
            'customers_assigned',
            'customers_unassigned',
            'customer_orders_pending',
            'cylinders_distributed_agents',
            'agents_with_distribution',
            'deliveries_pending',
            'pickup_orders_pending',
            'total_drivers',
            'drivers_with_deliveries',
            'cylindersAssignedChart',
            'customerRegistrationsChart',
            'new_customers_this_week',
            'unassigned_cylinders',
            'new_employees_this_month'
        ));
    }

    public function getStatisticsData()
    {
        try {
            $databaseConnection = DB::getDriverName();
            $dateFunction = $databaseConnection === 'sqlite'
                ? "strftime('%Y-%m', updated_at)"
                : "DATE_FORMAT(updated_at, '%Y-%m')";

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
                'cylindersAssignedChart'     => $cylindersAssignedChart,
                'customerRegistrationsChart' => $customerRegistrationsChart
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
