<?php

namespace App\Http\Controllers;

use App\Models\Cylinder;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class DashboardController extends Controller
{
    public function showDashboard()
    {
        $user = Auth::user();

        if ($user->position === 'Manager') {
            $cylinders = Cylinder::paginate(10);
            return view('management.home', [
                'position'   => $user->position,
                'cylinders'  => $cylinders,
                'warehouses' => Warehouse::all(),
            ]);
        }

        $cylinders     = Cylinder::where('user_id', $user->id)->get();
        $totalCylinders = $cylinders->count();

        return view('dashboard.profile', compact('cylinders', 'totalCylinders'));
    }

    public function managementHome()
    {
        // Fetch cylinders from the database, paginated
        $cylinders  = Cylinder::orderBy('created_at')->paginate(10);

        // Fetch all warehouses for the location filter
        $warehouses = Warehouse::all();

        // Pass both to the view
        return view('management.home', compact('cylinders', 'warehouses'));
    }

    public function customerDashboard()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Count cylinders assigned to the current user
        $totalCylinders = DB::table('cylinders')->where('user_id', $user->id)->count();

        // Fetch orders for the logged-in user
        $orders = Order::where('first_name', $user->first_name)
                   ->where('last_name', $user->last_name)
                   ->get();

        return view('dashboard.profile', compact('totalCylinders', 'user', 'orders'));
    }
}
