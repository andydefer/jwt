<?php

use Illuminate\Support\Facades\Route;
use Andydefer\JwtAuth\Controllers\JwtAuthController;

Route::post('register', [JwtAuthController::class, 'register']);
Route::post('login', [JwtAuthController::class, 'login']);

Route::middleware('jwt.auth')->group(function () {
    Route::get('user', [JwtAuthController::class, 'user']);
    Route::post('logout', [JwtAuthController::class, 'logout']);
    Route::post('refresh', [JwtAuthController::class, 'refresh']);
    Route::get('token', [JwtAuthController::class, 'token']);
});
