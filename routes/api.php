<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by your RouteServiceProvider with
| the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('api')->group(function () {
    // Example test route to verify it's working:
    Route::get('/ping', function (Request $request) {
        return response()->json(['pong' => true]);
    });

    // Here you can add your own API routes…
});
