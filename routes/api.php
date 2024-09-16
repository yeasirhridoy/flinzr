<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Resources\CountryResource;
use Illuminate\Support\Facades\Route;

Route::middleware(ResponseMiddleware::class)->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/recover-password', [AuthController::class, 'recoverPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('login/google',function (){

    });

    Route::get('collections', [CollectionController::class, 'index']);
    Route::get('collections/{id}', [CollectionController::class, 'show']);
    Route::get('explore', [CollectionController::class, 'explore']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('tags', [TagController::class, 'index']);
    Route::get('colors', [ColorController::class, 'index']);
    Route::get('settings', [UserController::class, 'settings']);
    Route::get('countries',function (){
        return CountryResource::collection(\App\Models\Country::all());
    });

    Route::get('artists', [UserController::class, 'artists']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user', [AuthController::class, 'updateUser']);
        Route::post('/update-password', [AuthController::class, 'updatePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/otp', [AuthController::class, 'otp']);
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
        Route::post('/save-fcm-token', [AuthController::class, 'saveFcmToken']);

        Route::middleware(['verified'])->group(function (){
            Route::post('collections', [CollectionController::class, 'store']);
            Route::get('my-collections', [CollectionController::class, 'myCollections']);

            Route::get('favorites', [FavoriteController::class, 'index']);
            Route::post('favorites', [FavoriteController::class, 'store']);

            Route::post('artist-request', [UserController::class, 'artistRequest']);
            Route::get('artist-request', [UserController::class, 'myArtistRequest']);

            Route::post('purchase/coin',[PurchaseController::class,'purchaseCoin']);
            Route::post('purchase/filter',[PurchaseController::class,'purchaseFilter']);
            Route::post('gift/filter',[PurchaseController::class,'giftFilter']);
            Route::get('purchased-collections',[CollectionController::class,'purchasedCollections']);
            Route::get('gifted-collections',[CollectionController::class,'giftedCollections']);

            Route::post('follow', [FollowController::class, 'toggleFollow']);
            Route::get('followers', [FollowController::class, 'followers']);
            Route::get('followings', [FollowController::class, 'followings']);
        });
    });
});
