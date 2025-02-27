<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Delivery;
use App\Models\User;
use App\Models\Cylinder;

class DriversController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Ensure the user is a driver
        if ($user->position !== 'Driver') {
            return redirect()->route('dashboard')->withErrors(['Unauthorized access.']);
        }

        // Fetch deliveries assigned to the logged-in driver
        $deliveries = Delivery::where('driver', $user->first_name . ' ' . $user->last_name)
            ->orderBy('delivery_date', 'desc')
            ->paginate(10);

        return view('drivers.cylinders', compact('deliveries', 'user')); // Pass $user to the view
    }

    public function driverProfile($id)
    {
        $user = User::findOrFail($id); // Retrieve the user

        // Determine the correct layout based on user position
        if ($user->position == 'Manager') {
            $view = 'layouts.management-dashboard';  // Manager layout
        } elseif ($user->position == 'Driver') {
            $view = 'layouts.drivers-dashboard';  // Driver layout
        } else {
            $view = 'layouts.default-dashboard';  // Default layout, in case it's neither
        }

        // Fetch deliveries assigned to the driver
        $deliveries = Delivery::where('driver_id', $id)->paginate(10);

        // Fetch related cylinders
        $cylinders = Cylinder::whereIn('id', $deliveries->pluck('cylinder'))->get()->keyBy('id');

        // Count total assigned cylinders
        $totalCylinders = $deliveries->count();

        return view('drivers.profile', compact('user', 'deliveries', 'cylinders', 'totalCylinders'));
    }
}
