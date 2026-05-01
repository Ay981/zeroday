<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\UserResource;
use App\Services\ProgramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API V1 ROUTES (SANCTUM SPA CLEAN VERSION)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH (PUBLIC)
    |--------------------------------------------------------------------------
    */
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    /*
    |--------------------------------------------------------------------------
    | OTP FLOW (PUBLIC)
    |--------------------------------------------------------------------------
    */
    Route::post('/otp/send', [AuthController::class, 'generateAndSendOtpPublic']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOtpPublic']);
    Route::post('/register/verify', [AuthController::class, 'completeRegistration']);

    /*
    |--------------------------------------------------------------------------
    | PROTECTED ROUTES (SANCTUM SPA CORRECT WAY)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        /*
        |--------------------------
        | CURRENT USER
        |--------------------------
        */
        Route::get('/user', function (Request $request) {
            return new UserResource($request->user());
        });

        /*
        |--------------------------
        | LOGOUT
        |--------------------------
        */
        Route::post('/logout', [AuthController::class, 'logout']);

        /*
        |--------------------------
        | EVIDENCE IMAGE PROXY
        |--------------------------
        */
     Route::get('/evidence/{filename}', function (string $filename) {
    $path = 'evidence/' . $filename;

    // Change 'public' to whatever disk you upload to
    $disk = Storage::disk(config('filesystems.default'));

    if (! $disk->exists($path)) {
        abort(404);
    }

    return $disk->response($path);
    
        });

        /*
        |--------------------------
        | REPORTS
        |--------------------------
        */
        Route::controller(ReportController::class)->group(function () {
            Route::get('/reports', 'index');
            Route::get('/reports/{report}', 'show');
            Route::post('/reports', 'store');
            Route::patch('/reports/{report}', 'update');
            Route::delete('/reports/{report}', 'destroy');
        });

        /*
        |--------------------------
        | USER STATS
        |--------------------------
        */
        Route::get('/user/stats', [UserController::class, 'stats']);

        /*
        |--------------------------
        | PROGRAMS
        |--------------------------
        */
        Route::get('/programs', function (ProgramService $service) {
            return ProgramResource::collection(
                $service->listPrograms()
            );
        });

    });

});
