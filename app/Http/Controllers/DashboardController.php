<?php

namespace App\Http\Controllers;

use App\Models\Cylinder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function showDashboard()
    {
        $user = Auth::user();

        if ($user->position === 'Manager') {
            $cylinders = Cylinder::paginate(10);
            return view('management.home', [
                'position' => $user->position,
                'cylinders' => $cylinders,
            ]);
        }

        $cylinders = Cylinder::where('user_id', $user->id)->get();
        $totalCylinders = $cylinders->count();

        return view('dashboard.home', compact('cylinders', 'totalCylinders'));
    }

    public function managementHome()
    {
        // Fetch cylinders from the database
        $cylinders = Cylinder::orderBy('created_at')->paginate(10); // Ensure pagination is applied

        // Pass cylinders to the view
        return view('management.home', compact('cylinders'));
    }
}
