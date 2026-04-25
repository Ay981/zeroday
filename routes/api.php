<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\UserResource;
use App\Services\ProgramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PUBLIC ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
    });

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED ROUTES (Sanctum SPA)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum'])->group(function () {

        /*
        |--------------------------
        | AUTH
        |--------------------------
        */
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        /*
        |--------------------------
        | USER
        |--------------------------
        */
        Route::get('/user', function (Request $request) {
            return new UserResource($request->user());
        })->name('user.current');

        Route::get('/user/stats', [UserController::class, 'stats'])->name('user.stats');

        /*
        |--------------------------
        | REPORTS
        |--------------------------
        */
        Route::controller(ReportController::class)->group(function () {
            Route::get('/reports', 'index')->name('reports.index');
            Route::get('/reports/{report}', 'show')->name('reports.show');

            Route::post('/reports', 'store')->name('reports.store');
            Route::patch('/reports/{report}', 'update')->name('reports.update');
            Route::delete('/reports/{report}', 'destroy')->name('reports.destroy');
        });

        /*
        |--------------------------
        | PROGRAMS
        |--------------------------
        */
        Route::get('/programs', function (ProgramService $service) {
            return ProgramResource::collection(
                $service->listPrograms()
            );
        })->name('programs.index');
    });
});