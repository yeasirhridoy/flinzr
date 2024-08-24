<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/recover-password', [AuthController::class, 'recoverPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('collections', [CollectionController::class, 'index']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('tags',[TagController::class,'index']);
Route::get('colors',[ColorController::class,'index']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'updateUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/otp', [AuthController::class, 'otp']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

    Route::post('collections', [CollectionController::class, 'store']);
});
