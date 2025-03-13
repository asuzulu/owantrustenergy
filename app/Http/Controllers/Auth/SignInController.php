<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignInController extends Controller
{
    public function showSignInForm()
    {
        return view('sign-in');
    }

    public function store(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $user = Auth::user();

            if ($user->wasRecentlyCreated) {
                return redirect()->route('dashboard.profile');
            }

            return $this->redirectUserByPosition($user);
        }

        return redirect()->back()->with('login_error', 'Invalid email or password.');
    }

    // Redirect the user based on their position
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
                return redirect()->route('dashboard.agent');
            case 'driver':
                return redirect()->route('drivers.cylinders');
            default:
                Auth::logout();
                return redirect('/sign-in')->route('signin.form')->withErrors(['Invalid role.']);
        }
    }

    public function customLogout(Request $request)
    {
        Auth::logout();
        return redirect()->route('home');
    }
}
