<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'updateUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/otp', [AuthController::class, 'otp']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/recover-password', [AuthController::class, 'recoverPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
