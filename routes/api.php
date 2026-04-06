<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;


Route::post("/login", [AuthController::class, 'login'])->name('login');
Route::post('/logout',[AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
Route::get('/reports', [ReportController::class, 'index'])->middleware('auth:sanctum')->name('reports.index');
Route::get('reports/{report}', [ReportController::class, 'show'])->middleware('auth:sanctum')->name('reports.show');


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
