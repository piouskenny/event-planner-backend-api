<?php

use App\Http\Controllers\EventsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\AuthController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1/user')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
        return $request->user();
    });

    /**
     * Events endpoint
     */
     Route::prefix('/event')->middleware('auth:sanctum')->group(function () {
        Route::post('/create', [EventsController::class, 'store']);
        Route::get('/show', [EventsController::class, 'index']);
        Route::get('/show/{id}', [EventsController::class, 'show']);
        Route::put('/update/{id}', [EventsController::class, 'update']);
        Route::delete('/delete/{id}', [EventsController::class, 'destroy']);
    });
});


//view event for unauthorized user
Route::prefix('/v1/event')->group(function () {
    Route::get('/{slug}', [EventsController::class, 'showByUrl']);
    Route::get('/fetch/types', [EventsController::class, 'getEventTypes']);
    Route::get('/fetch/tags', [EventsController::class, 'getEventTags']);
});
