<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // This is the correct block for middleware configuration
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdminMiddleware::class,
            'lender' => \App\Http\Middleware\IsLenderMiddleware::class,
        ]);

        // This code needs to be moved here
        $middleware->validateCsrfTokens(except: [
            'api/webhooks/payhero',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // This block should be empty for now
    })->create();