<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignInController extends Controller
{
    // Show the sign-in form
    public function showSignInForm()
    {
        return view('sign-in');
    }

    // Handle the login process
    public function store(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Attempt login with provided credentials and remember option
        if (Auth::attempt($credentials, $request->has('remember'))) {
            $user = Auth::user();

            // Redirect the user based on role (position)
            return $this->redirectUserByPosition($user);
        }

        // If login fails, return back with error
        return redirect()->back()->with('login_error', 'Invalid email or password.');
    }

    // Redirect the user based on their role (position)
    private function redirectUserByPosition($user)
    {
        switch (strtolower($user->position)) {
            case 'customer':
                return redirect()->route('dashboard.profile');
            case 'employee':
                return redirect()->route('dashboard.employee');
            case 'manager':
                return redirect()->route('management.home');
            case 'agent':
                return redirect()->route('agent.dashboard');
            case 'driver':
                return redirect()->route('drivers.cylinders');
            default:
                Auth::logout();
                return redirect()->route('signin.form')->withErrors(['Invalid role.']);
        }
    }
}
