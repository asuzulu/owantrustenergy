<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Handle an attempt to access user data when the user is not authenticated.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleUserNotAuthenticated()
    {
        // Redirect to the home page
        return redirect()->route('home');
    }
}
