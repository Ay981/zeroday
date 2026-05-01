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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\TrustProxies::class);
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // ✅ THIS is what adds StartSession + EnsureFrontendRequestsAreStateful to API routes
        $middleware->statefulApi();

        // ✅ CSRF exceptions are now unnecessary for API routes since statefulApi handles it
        // but keep them if you want to be explicit
        $middleware->validateCsrfTokens(except: [
            'api/v1/login',
            'api/v1/register',
            'api/v1/otp/send',
            'api/v1/otp/verify',
            'api/v1/register/verify',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();