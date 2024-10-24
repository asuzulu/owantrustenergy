<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\State; // Make sure to import the State model
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function showRegisterForm()
    {
        $states = State::all(); // Fetch all states
        return view('register', compact('states'));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:15',
            'gender' => 'required|string',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id', // Ensure state exists
            'bvn' => 'required|string|size:11',
            'nin' => 'required|string|size:11',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create the user
        User::create([
            'first_name' => $validatedData['firstName'],
            'last_name' => $validatedData['lastName'],
            'phone_number' => $validatedData['phoneNumber'],
            'gender' => $validatedData['gender'],
            'street' => $validatedData['street'],
            'city' => $validatedData['city'],
            'state_id' => $validatedData['state_id'],
            'bvn' => $validatedData['bvn'],
            'nin' => $validatedData['nin'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        return redirect()->route('register.success');
    }
}
