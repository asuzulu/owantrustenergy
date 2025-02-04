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
    public function logout()
    {
        Auth::logout(); // Log out the user
        return redirect()->route('login'); // Redirect to the login page
    }

    // Determine the correct dashboard based on user position
    private function redirectUserByPosition($user)
    {
        if ($user->position === 'customer') {
            return redirect()->route('dashboard.customer');
        } elseif ($user->position === 'employee') {
            return redirect()->route('dashboard.employee');
        } elseif ($user->position === 'Manager') {
            return redirect()->route('dashboard.management');
        }
        return redirect()->route('dashboard.home'); // Default fallback
    }
}
