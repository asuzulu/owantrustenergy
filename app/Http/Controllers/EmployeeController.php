<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\State;

class EmployeeController extends Controller
{
    public function index()
    {
        if (Auth::user()->position === 'Customer') {
            abort(403, 'Unauthorized action.');
        }
        try {
            $employees = User::where('position', 'Employee')->paginate(10);
            $states = State::all();
        } catch (\Exception $e) {
            Log::debug('Error retrieving Employees: ', ['error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve employees.');
        }
        // Updated view path to match resources/views/management/employees.blade.php
        return view('management.employees', compact('employees', 'states'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        // If you don't have any actual data for warehouse cylinders, pass an empty collection
        $warehouseCylinders = collect();
        return view('users.profile', compact('user', 'warehouseCylinders'));
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'firstName'         => 'required|string|max:255',
            'lastName'          => 'required|string|max:255',
            'phoneNumber'       => 'required|string|max:15',
            'gender'            => 'required|string',
            'street'            => 'required|string|max:255',
            'city'              => 'required|string|max:255',
            'state'             => 'required|exists:states,id',
            'bvn'               => 'required|digits:11',
            'nin'               => 'required|digits:11',
            'email'             => 'required|email|unique:users,email',
            'dob'               => 'required|date|before:today',
            'password'          => 'required|string|min:8|confirmed',
            'position'    => 'required|string|in:Employee',
        ]);

        try {
            // Retrieve the state name from the states table using the provided state ID
            $stateName = State::where('id', $validatedData['state'])->value('name');

            $employee = User::create([
                'first_name'   => $validatedData['firstName'],
                'last_name'    => $validatedData['lastName'],
                'phone_number' => $validatedData['phoneNumber'],
                'gender'       => $validatedData['gender'],
                'street'       => $validatedData['street'],
                'city'         => $validatedData['city'],
                'state'        => $stateName,
                'bvn'          => $validatedData['bvn'],
                'nin'          => $validatedData['nin'],
                'email'        => $validatedData['email'],
                'dob'          => $validatedData['dob'],
                'password'     => Hash::make($validatedData['password']),
                'position'     => $validatedData['position'],
            ]);

            return redirect()->route('employees.index')->with('success', 'Employee added successfully.');
        } catch (\Exception $e) {
            Log::debug('Error adding Employee: ', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to add employee.');
        }
    }
}
