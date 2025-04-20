<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return view('management-portal'); // This should match your login page view
    }

    // Handle user authentication
    public function authenticate(Request $request)
    {
        // Validate login credentials
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Attempt to authenticate the user
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user(); // Get the authenticated user

            // Redirect based on the user's position in the database
            return $this->redirectUserByPosition($user);
        }

        // Redirect back with an error message if authentication fails
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    // Handle user logout
    public function logout(Request $request)
    {
        $user = Auth::user();
        Auth::logout();

        if ($user) {
            if ($user->position === 'Customer') {
                return redirect()->route('dashboard.profile');
            } elseif ($user->position === 'Employee') {
                return redirect()->route('dashboard.employee');
            } elseif ($user->position === 'Manager') {
                return redirect()->route('management.home');
            } elseif ($user->position === 'Agent') {
                return redirect()->route('agent.dashboard');
            } elseif ($user->position === 'Driver') {
                return redirect()->route('dashboard.driver');
            }
        }

        return redirect()->route('home');
    }

    // Determine the correct dashboard based on user position
    private function redirectUserByPosition($user)
    {
        if ($user->position === 'Customer') {
            return redirect()->route('dashboard.profile');
        } elseif ($user->position === 'Employee') {
            return redirect()->route('dashboard.employee');
        } elseif ($user->position === 'Manager') {
            return redirect()->route('management.home');
        } elseif ($user->position === 'Agent') {
            return redirect()->route('agent.dashboard');
        } elseif ($user->position === 'Driver') {
            return redirect()->route('dashboard.driver');
        }
    }
}

