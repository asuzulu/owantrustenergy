<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Cylinder;

class EmployeeController extends Controller
{
    public function dashboard()
    {
        $cylinders = Cylinder::paginate(10); // Paginate cylinders, 10 per page
        return view('management.home', compact('cylinders'));
    }
    public function accounts()
    {
        // Fetch all users with the role 'customer'
        $users = User::where('position', 'Customer')->get();  // Ensure 'Customer' is case-sensitive match for the role column

        return view('management.accounts', compact('users'));
    }

    public function agents()
    {
        // Fetch all users with the role 'Agent'
        $agents = User::where('position', 'Agent')->get();

        return view('management.agents', compact('agents'));
    }

    public function cylindersPage()
    {
        // Fetch paginated cylinders from the database (10 per page)
        $cylinders = Cylinder::paginate(10);

        // Pass the paginated cylinders to the view
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
            $user->assignRole('Employee'); // Correct method for assigning roles
        }

        return redirect()->route('users.profile', $user->id)->with('success', 'Cylinder assigned and role updated successfully!');
    }

    public function statistics()
    {
        return view('management.statistics');
    }
}
