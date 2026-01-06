<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\TicketController;
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
    // get ticket details
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
});

// Admin Only
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // create event
    Route::post('/events', [EventController::class, 'store']);
    // toggle event status
    Route::patch('/events/{event}', [EventController::class, 'toggle']);
});

// Attendee Only
Route::middleware(['auth:sanctum', 'role:attendee'])->group(function () {
    // create ticket
    Route::post('/tickets', [TicketController::class, 'store']);
    // cancel ticket
    Route::patch('/tickets/{ticket}', [TicketController::class, 'cancel']);
    // get list of tickets
    Route::get('/tickets', [TicketController::class, 'index']);
});