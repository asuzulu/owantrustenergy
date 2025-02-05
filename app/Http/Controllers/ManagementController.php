<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cylinder;
use Spatie\Permission\Models\Role;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
    public function accounts()
    {
        // Fetch all users with the role 'customer'
        $users = User::where('position', 'Customer')->get();
        return view('management.accounts', compact('users'));
    }

    public function employees()
    {
        // Fetch all users with the role 'employee'
        $employees = User::where('position', 'Employee')->get();
        return view('management.employees', compact('employees'));
    }

    public function agents()
    {
        // Fetch all users with the role 'Agent'
        $agents = User::where('position', 'Agent')->get();
        return view('management.agents', compact('agents'));
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
        return view('management.statistics');
    }
}
