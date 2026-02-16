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
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    // Time entry routes
    Route::get('/time-entries/today', [TimeEntryController::class, 'today']);
    Route::get('/time-entries/current-week', [TimeEntryController::class, 'currentWeek']);
    Route::get('/time-entries/current-month', [TimeEntryController::class, 'currentMonth']);
    Route::post('/time-entries/clock-in', [TimeEntryController::class, 'clockIn']);
    Route::post('/time-entries/clock-out', [TimeEntryController::class, 'clockOut']);
    Route::put('/time-entries/{timeEntry}', [TimeEntryController::class, 'update']);
    Route::delete('/time-entries/{timeEntry}', [TimeEntryController::class, 'destroy']);
});
