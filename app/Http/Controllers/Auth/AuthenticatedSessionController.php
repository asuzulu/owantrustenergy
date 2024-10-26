<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    // Method for logging out
    public function destroy(Request $request)
    {
        Auth::logout();
        return redirect()->route('home'); // Redirect to the desired route after logout
    }
}
