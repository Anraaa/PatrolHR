<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies (required for ngrok / reverse proxies / Docker behind nginx)
        // Ensures X-Forwarded-Proto, X-Forwarded-Host, etc. are respected so Laravel
        // generates correct HTTPS URLs and Livewire AJAX works properly.
        $middleware->trustProxies(at: '*');

        // Redirect unauthenticated users to Filament admin login
        // (default Laravel 'login' route does not exist in this project)
        $middleware->redirectGuestsTo(fn () => url('/admin/login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
