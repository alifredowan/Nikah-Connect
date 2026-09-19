<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DiscoveryApiController;
use App\Http\Controllers\Api\V1\InterestApiController;
use App\Http\Controllers\Api\V1\MessageApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected API Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Discovery & Match Engine
        Route::get('/discover', [DiscoveryApiController::class, 'index']);
        Route::get('/discover/daily-matches', [DiscoveryApiController::class, 'dailyMatches']);
        Route::get('/profiles/{userId}', [DiscoveryApiController::class, 'show']);

        // Interests
        Route::get('/interests', [InterestApiController::class, 'index']);
        Route::post('/interests/{recipientId}', [InterestApiController::class, 'store']);
        Route::post('/interests/{id}/respond', [InterestApiController::class, 'respond']);

        // Halal Messaging
        Route::get('/conversations', [MessageApiController::class, 'conversations']);
        Route::get('/conversations/{id}/messages', [MessageApiController::class, 'messages']);
        Route::post('/conversations/{id}/messages', [MessageApiController::class, 'send']);
    });
});
