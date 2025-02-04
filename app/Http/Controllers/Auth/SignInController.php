<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignInController extends Controller
{
    public function showSignInForm($portal)
    {
        $validPortals = ['customer', 'employee', 'management'];
        if (!in_array($portal, $validPortals)) {
            abort(404);
        }

        return view("$portal-portal");
    }

    public function store(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $portal = $request->input('portal');

        if (!in_array($portal, ['customer', 'employee', 'management'])) {
            return redirect()->back()->withErrors(['Invalid portal specified.']);
        }

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $user = Auth::user();

            // Map user position to portal role
            $roleMap = [
                'customer' => 'Customer',
                'employee' => 'Employee',
                'management' => 'Manager',
            ];

            if ($user->position !== $roleMap[$portal]) {
                Auth::logout();
                return redirect()->back()->withErrors(['You do not have access to this portal.']);
            }

            // Redirect to the correct dashboard route based on portal
            switch ($portal) {
                case 'customer':
                    return redirect()->route('dashboard.home');
                case 'employee':
                    return redirect()->route('employee.home'); // Ensure this route exists
                case 'management':
                    return redirect()->route('management.home');
            }
        }

        return redirect()->back()->withErrors(['Invalid credentials.']);
    }

    public function customLogout(Request $request)
    {
        Auth::logout();
        return redirect()->route('home');
    }
}
