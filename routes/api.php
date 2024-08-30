<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ResponseMiddleware;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::middleware(ResponseMiddleware::class)->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/recover-password', [AuthController::class, 'recoverPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('login/google',function (){

    });

    Route::get('collections', [CollectionController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('tags', [TagController::class, 'index']);
    Route::get('colors', [ColorController::class, 'index']);

    Route::get('artists', [UserController::class, 'artists']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user', [AuthController::class, 'updateUser']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/otp', [AuthController::class, 'otp']);
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

        Route::post('collections', [CollectionController::class, 'store']);

        Route::get('favorites', [FavoriteController::class, 'index']);
        Route::post('favorites', [FavoriteController::class, 'store']);

        Route::post('artist-request', [UserController::class, 'artistRequest']);
        Route::get('artist-request', [UserController::class, 'myArtistRequest']);

        Route::post('purchase/filter',[PurchaseController::class,'purchaseFilter']);
    });
});