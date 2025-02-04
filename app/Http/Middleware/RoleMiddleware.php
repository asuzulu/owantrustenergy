<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Ensure user is authenticated
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has the required role
        if (!$user->hasRole($role)) {
            return redirect()->route('home'); // or some other action
        }

        return $next($request);
    }
}
