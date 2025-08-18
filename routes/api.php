<?php

use Illuminate\Support\Facades\Route;
use AndyDefer\Jwt\Controllers\JwtAuthController;

// Routes publiques
Route::post('/register', [JwtAuthController::class, 'register'])->name('jwt.register');
Route::post('/login', [JwtAuthController::class, 'login'])->name('jwt.login');
Route::get('/token', [JwtAuthController::class, 'getToken'])->name('jwt.token');

// Routes protégées
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/logout', [JwtAuthController::class, 'logout'])->name('jwt.logout');
    Route::post('/refresh', [JwtAuthController::class, 'refresh'])->name('jwt.refresh');
    Route::post('/verify-signature', [JwtAuthController::class, 'verifySignature'])->name('jwt.verifySignature');

    // Route pour récupérer l'utilisateur via JWT
    Route::get('/user', [JwtAuthController::class, 'user'])->name('jwt.user');
});
