<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
Route::get('/reports', [ReportController::class, 'index'])->middleware('auth:sanctum')->name('reports.index');
Route::get('reports/{report}', [ReportController::class, 'show'])->middleware('auth:sanctum')->name('reports.show');
Route::post('/reports', [ReportController::class, 'store'])->middleware('auth:sanctum')->name('reports.store');
Route::patch('/reports/{report}', [ReportController::class, 'update'])->middleware('auth:sanctum')->name('reports.update');
Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->middleware('auth:sanctum')->name('reports.destroy');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
