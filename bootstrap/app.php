<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies('*');  // Trust all proxies

        // Register custom middleware aliases
        $middleware->alias([
            'VerifyCsrfToken' => \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, // Corrected fully qualified class name
            'role' => RoleMiddleware::class, // Use the correct middleware here
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Define exception handling logic here if needed
    })
    ->create();
