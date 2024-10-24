<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the login form (optional, in case you want a dedicated route)
    public function showLoginForm()
    {
        return view('signin');
    }

    // Handle sign-in form submission
    public function authenticate(Request $request)
    {
        // Validate the form data
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt to log the user in
        if (Auth::attempt($credentials)) {
            // Authentication passed, regenerate session to prevent session fixation attacks
            $request->session()->regenerate();

            // Redirect to the intended page or home after successful login
            return redirect()->intended('/');
        }

        // Authentication failed, redirect back with input and error message
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handle logout (optional, if needed)
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
