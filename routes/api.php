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
    // No routes here for now
});

/*
|--------------------------------------------------------------------------
| API ROUTES (No Web Middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['web'])->prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | WEB ROUTES (with CSRF/Session)
    |--------------------------------------------------------------------------
    */
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    
    /*
    |--------------------------------------------------------------------------
    | PUBLIC OTP VERIFICATION ROUTES (no auth required)
    |--------------------------------------------------------------------------
    */
    Route::post('/otp/verify', [AuthController::class, 'verifyOtpPublic'])->name('otp.verify.public');
    Route::post('/register/verify', [AuthController::class, 'completeRegistration'])->name('register.verify');
    
    /*
    |--------------------------------------------------------------------------
    | SESSION AUTHENTICATED ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])->group(function () {
        Route::get('/user', function (Request $request) {
            return new UserResource($request->user());
        })->name('user.current');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
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
        | USER STATS
        |--------------------------
        */
        Route::get('/user/stats', [UserController::class, 'stats'])->name('user.stats');
    });
});

/*
|--------------------------------------------------------------------------
| API ROUTES (No Web Middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['api'])->prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | SEMI-PUBLIC ROUTES (OTP Flow)
    |--------------------------------------------------------------------------
    */
    Route::post('/otp/send', [AuthController::class, 'generateAndSendOtpPublic'])->name('otp.send.public');

    /*
    |--------------------------------------------------------------------------
    | WEB ROUTES (with CSRF/Session)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['web'])->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        
        /*
        |--------------------------------------------------------------------------
        | PUBLIC OTP VERIFICATION ROUTES (no auth required)
        |--------------------------------------------------------------------------
        */
        Route::post('/otp/verify', [AuthController::class, 'verifyOtpPublic'])->name('otp.verify.public');
        Route::post('/register/verify', [AuthController::class, 'completeRegistration'])->name('register.verify');
        
        /*
        |--------------------------------------------------------------------------
        | SESSION AUTHENTICATED ROUTES
        |--------------------------------------------------------------------------
        */
        Route::middleware(['auth'])->group(function () {
            Route::get('/user', function (Request $request) {
                return new UserResource($request->user());
            })->name('user.current');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            
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
            | USER STATS
            |--------------------------
            */
            Route::get('/user/stats', [UserController::class, 'stats'])->name('user.stats');
            
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
});
