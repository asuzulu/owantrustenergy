<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (app()->environment('local')) {
            DB::listen(function ($query) {
                \Log::info("Query executed: " . $query->sql);
            });
        }
    }

    public function register()
    {
        //
    }
}
