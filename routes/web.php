<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportsController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/reports', [ReportsController::class, 'index']);