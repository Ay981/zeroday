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
        // 1. ELITE MOVE: Trust ALL proxies for Render/Vercel
        // This ensures Laravel knows the request is HTTPS even when coming from a load balancer.
        $middleware->trustProxies(at: '*');

        // 2. DO NOT manually append HandleCors. 
        // Laravel 11/12/13 includes it by default. 
        // Manually appending can cause it to run in the wrong order.

        // 3. Enable SPA Cookie Auth
        $middleware->statefulApi();

        // 4. CSRF Exceptions
        $middleware->validateCsrfTokens(except: [
            'api/v1/login',
            'api/v1/register',
            'api/v1/otp/send',
            'api/v1/otp/verify',
            'api/v1/register/verify',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();