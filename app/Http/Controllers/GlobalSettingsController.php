<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlobalSetting;

class GlobalSettingsController extends Controller
{
    // Only Managers should access these routes.
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Manager'); // Assumes you have middleware for role checking
    }

    // Display the global settings page
    public function index()
    {
        $settings = GlobalSetting::all();
        return view('manager.global_settings', compact('settings'));
    }

    // Handle updates to the global settings
    public function update(Request $request)
    {
        // Loop through posted settings. Each checkbox returns 'on' if checked.
        foreach ($request->except('_token') as $key => $value) {
            GlobalSetting::set($key, $value === 'on');
        }

        return redirect()->back()->with('success', 'Global settings have been updated.');
    }
}
