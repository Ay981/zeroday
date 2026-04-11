<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\UserResource;
use App\Services\ProgramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ---------------------------------------------------------
    // TIER 1: PUBLIC / GUEST ROUTES
    // Protected by 'auth' rate limiter (Max 5 attempts/min)
    // ---------------------------------------------------------
    Route::middleware(['throttle:auth'])->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
    });

    // ---------------------------------------------------------
    // TIER 2: AUTHENTICATED ROUTES (The Security Perimeter)
    // All routes below this line require a valid Sanctum Token
    // ---------------------------------------------------------
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // SUB-TIER: DATA BROWSING (Read-Only / API Tier)
        // Protected by 'api' rate limiter (Max 60 requests/min)
        Route::middleware(['throttle:api'])->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
            Route::get('/programs', function (ProgramService $service) {
                return ProgramResource::collection($service->listPrograms());
            })->name('programs.index');

            Route::get('/user', function (Request $request) {
                return new UserResource($request->user());
            })->name('user.current');
            
            Route::get('/user/stats', [UserController::class, 'stats'])->name('user.stats');
        });

        // SUB-TIER: MUTATIONS (Write-heavy / Upload Tier)
        // Protected by 'uploads' rate limiter (Max 10 requests/min)
        Route::middleware(['throttle:uploads'])->group(function () {
            Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
            Route::patch('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
            Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
        });
    });
});