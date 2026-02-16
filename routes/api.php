<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TimeEntryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Time entry routes
    Route::get('/time-entries/today', [TimeEntryController::class, 'today']);
    Route::post('/time-entries/clock-in', [TimeEntryController::class, 'clockIn']);
    Route::post('/time-entries/clock-out', [TimeEntryController::class, 'clockOut']);
});
