<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cylinder;
use App\Models\State;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManagementController extends Controller
{
    public function index()
    {
        $cylinders = Cylinder::orderBy('id')->paginate(10);
        return view('management.home', compact('cylinders'));
    }

    public function accounts()
    {
        // Fetch all users with the role 'customer'
        $users = User::where('position', 'Customer')->get();

        // Fetch states for the Add Customer form
        $states = State::all();

        return view('management.accounts', compact('users', 'states'));
    }

    public function employees()
    {
        // Fetch all users with the role 'employee'
        $employees = User::where('position', 'Employee')->get();
        return view('management.employees', compact('employees'));
    }

    public function agents()
    {
        try {
            $agents = User::where('position', 'Agent')->paginate(10);
            $states = State::all();  // Retrieve all states
        } catch (\Exception $e) {
            Log::error('Error retrieving agents: ' . $e->getMessage());
            abort(500, 'Error retrieving agents.');
        }

        return view('management.agents', compact('agents', 'states'));
    }

    public function cylindersPage()
    {
        $cylinders = Cylinder::orderBy('id')->paginate(10);
        return view('management.home', compact('cylinders'));
    }


    public function assignCylinder(Request $request, User $user)
    {
        $request->validate([
            'cylinder_id' => 'required|exists:cylinders,id',
        ]);

        $user->assignedCylinder()->associate(Cylinder::find($request->cylinder_id));
        $user->save();

        // Assign the 'Employee' role to the user if not already assigned
        if (!$user->hasRole('Employee')) {
            $user->assignRole('Employee');
        }

        return redirect()->route('users.profile', $user->id)->with('success', 'Cylinder assigned and role updated successfully!');
    }

    public function statistics()
    {
        // Fetch assigned cylinders per month
        $cylindersAssignedChart = Cylinder::selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
            ->whereNotNull('user_id') // Only assigned cylinders
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fetch new customer registrations per month
        $customerRegistrationsChart = User::selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
            ->where('position', 'Customer')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('management.statistics', compact('cylindersAssignedChart', 'customerRegistrationsChart'));
    }
    public function drivers()
    {
        if (Auth::user()->position === 'Customer') {
            abort(403, 'Unauthorized action.');
        }
        try {
            // Use paginate(10) so that $drivers is a paginator, not a collection
            $drivers = User::where('position', 'Driver')->paginate(10);
            $states = State::all();
        } catch (\Exception $e) {
            Log::debug('Error retrieving Drivers: ', ['error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve drivers.');
        }
        return view('management.drivers', compact('drivers', 'states'));
    }

    public function showCylinder($id)
    {
        $cylinder = Cylinder::findOrFail($id);
        $warehouses = DB::table('warehouses')->get();
        $deliveries = Delivery::where('cylinder', $id)->get();

        return view('cylinders.show', compact('cylinder', 'warehouses', 'deliveries'));
    }
}
