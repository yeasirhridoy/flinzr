<?php

use App\Enums\Plan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PayoutMethodController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckActiveUser;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\CountryResource;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\SpecialRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::get('/get-subscription/{id}',function ($id){
    $baseUrl = config('app.revenuecat_url');
    $url = $baseUrl . "/subscribers/" . $id;
    $apiKey = config('app.revenuecat_api_key');

    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        }

        return ['success' => false, 'error' => $response->body()];
    } catch (\Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
});

Route::middleware(ResponseMiddleware::class)->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/recover-password', [AuthController::class, 'recoverPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/register-device', [AuthController::class, 'registerDevice']);

    Route::get('login/google', function () {

    });

    Route::get('collections', [CollectionController::class, 'index']);
    Route::get('collections/{id}', [CollectionController::class, 'show']);
    Route::get('explore', [CollectionController::class, 'explore']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('tags', [TagController::class, 'index']);
    Route::get('colors', [ColorController::class, 'index']);
    Route::get('settings', [UserController::class, 'settings']);
    Route::get('countries', function () {
        return CountryResource::collection(Country::query()->whereHas('regions', function ($query) {
            $query->active();
        })->active()->get());
    });
    Route::get('plans', function () {
        return collect(Plan::cases())->map(fn($plan) => [
            'name' => $plan->getLabel(),
            'price' => $plan->getPrice(),
            'features' => $plan->getFeatures()
        ]);
    });
    Route::get('countries/{code}', function ($code) {
        return new CountryResource(Country::where('code', $code)->firstOrFail());
    });

    Route::get('artists', [UserController::class, 'artists']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::get('artist-follow', [UserController::class, 'artistFollow']);


    Route::middleware(['auth:sanctum', CheckActiveUser::class])->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user', [AuthController::class, 'updateUser']);
        Route::get('/artist-setting', [UserController::class, 'artistSetting']);
        Route::post('/update-password', [AuthController::class, 'updatePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/otp', [AuthController::class, 'otp']);
        Route::post('/save-fcm-token', [AuthController::class, 'saveFcmToken']);

        Route::middleware(['verified'])->group(function () {
            Route::post('collections', [CollectionController::class, 'store']);
            Route::get('my-collections', [CollectionController::class, 'myCollections']);

            Route::get('favorites', [FavoriteController::class, 'index']);
            Route::post('favorites', [FavoriteController::class, 'store']);

            Route::post('artist-request', [UserController::class, 'artistRequest']);
            Route::get('artist-request', [UserController::class, 'myArtistRequest']);

            Route::post('purchase/coin', [PurchaseController::class, 'purchaseCoin']);
            Route::post('purchase/filter', [PurchaseController::class, 'purchaseFilter']);
            Route::post('gift/filter', [PurchaseController::class, 'giftFilter']);
            Route::get('purchased-collections', [CollectionController::class, 'purchasedCollections']);
            Route::get('gifted-collections', [CollectionController::class, 'giftedCollections']);
            Route::get('gifted-filters', [CollectionController::class, 'giftedFilters']);

            Route::get('subscription-feature', [PurchaseController::class, 'subscriptionFeature']);
            Route::get('monthly-used-counter', [PurchaseController::class, 'monthlyUsedCounter']);
            Route::get('counter', [PurchaseController::class, 'profileCounter']);

            Route::post('follow', [FollowController::class, 'toggleFollow']);
            Route::get('followers', [FollowController::class, 'followers']);
            Route::get('followings', [FollowController::class, 'followings']);
            Route::get('follow', [FollowController::class, 'follow']);

            Route::post('subscription', [SubscriptionController::class, 'store']);

            Route::post('daily-coin', [SubscriptionController::class, 'dailyCoin']);
            Route::get('influencer-request', [RequestController::class, 'getInfluencerRequest']);
            Route::post('influencer-request', [RequestController::class, 'storeInfluencerRequest']);
            Route::post('payout-request', [RequestController::class, 'storePayoutRequest']);
            Route::post('payout-method', [PayoutMethodController::class, 'store']);
            Route::post('special-request', [RequestController::class, 'storeSpecialRequest']);
            Route::get('special-request', [RequestController::class, 'getSpecialRequest']);

            Route::post('chat', function (Request $request) {
                $rules = [
                    'special_request_id' => 'required|exists:special_requests,id',
                    'message' => 'string|required_without:attachments',
                    'attachments' => 'array|required_without:message',
                    'attachments.*' => 'string'
                ];

                $request->validate($rules);

                if ($request->has('attachments')) {
                    $attachments = [];
                    foreach ($request->attachments as $attachment) {
                        $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $attachment));
                        $imageName = Str::random(32);
                        $s3Path = 'special-requests/' . $imageName;
                        Storage::put($s3Path, $image, 'public');
                        $attachments[] = $s3Path;
                    }
                }

                $conversation = Conversation::create([
                    'conversationable_id' => $request->special_request_id,
                    'conversationable_type' => SpecialRequest::class,
                    'message' => $request->message,
                    'attachments' => $attachments ?? [],
                    'sender' => 'user'
                ]);

                return ConversationResource::make($conversation);
            });
            Route::get('chat/{id}', function ($id) {
                $specialRequest = SpecialRequest::findOrFail($id);
                $conversations = $specialRequest->conversations()->get();
                return ConversationResource::collection($conversations);
            });
        });
    });
});
