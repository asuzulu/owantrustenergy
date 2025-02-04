<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cylinder;

class CustomerController extends Controller
{
    public function showCylinders()
    {
        $user = Auth::user();

        // Fetch cylinders assigned to the logged-in user
        $cylinders = Cylinder::where('user_id', $user->id)->paginate(10);

        return view('dashboard.cylinder', compact('cylinders'));
    }
}
