<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContainerController;

Route::prefix('v1')->group(function () {
    
    // Endpoint Public (Tanpa Token)
    Route::post('/login', [AuthController::class, 'login']);

    // Endpoint Terproteksi JWT (Wajib Login)
    Route::middleware('auth:api')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // API Gateway untuk Resource WowoClean
        Route::prefix('gateway')->group(function () {
            
            // Akses Read-Only (Admin & User)
            Route::get('/containers', [ContainerController::class, 'index']);
            Route::get('/containers/search', [ContainerController::class, 'search']);
            Route::get('/containers/{id}/logs', [ContainerController::class, 'logs']);

            // Akses Manipulasi Data (Khusus Admin)
            Route::middleware('role:admin')->group(function () {
                Route::post('/containers', [ContainerController::class, 'store']);
                Route::patch('/containers/{id}/archive', [ContainerController::class, 'archive']);
                Route::delete('/containers/{id}', [ContainerController::class, 'destroy']);
            });
        });
    });
});