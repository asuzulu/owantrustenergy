<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cylinder;

class CustomerController extends Controller
{
    public function showCylinders()
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to view your cylinders.');
        }

        $user = Auth::user();

        // Fetch cylinders assigned to the logged-in user
        $cylinders = Cylinder::where('user_id', $user->id)->paginate(10);

        return view('dashboard.cylinder', compact('cylinders'));
    }

    // Show cylinder details for the logged-in user
    public function show($id)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to view cylinder details.');
        }

        $user = Auth::user();

        // Check if the cylinder is assigned to the logged-in user
        $cylinder = Cylinder::where('user_id', $user->id)->findOrFail($id);

        return view('cylinders.show', compact('cylinder'));
    }
}
