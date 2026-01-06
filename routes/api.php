<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Authenticated Only
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // get list of events
    Route::get('/events', [EventController::class, 'index']);
    // get single event
    Route::get('/events/{event}', [EventController::class, 'show']);
});

// Admin Only
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/events', [EventController::class, 'store']);
});

