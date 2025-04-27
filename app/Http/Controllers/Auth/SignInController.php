<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
        // Validate inputs
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Normalize and extract credentials
        $email    = strtolower($request->input('email'));
        $password = $request->input('password');

        // Find user by case‐insensitive email
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        // Check password and log in
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $request->has('remember'));
            return $this->redirectUserByPosition($user);
        }

        // On failure, show error modal
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

    // Custom logout logic
    public function customLogout(Request $request)
    {
        Auth::logout();
        return redirect()->route('home');
    }
}
