<?php

namespace App\Http\Controllers;

use App\Enums\CommissionLevel;
use App\Enums\Price;
use App\Enums\SalesType;
use App\Http\Requests\CoinPurchaseRequest;
use App\Http\Requests\GiftFilterRequest;
use App\Http\Requests\PurchaseFilterRequest;
use App\Models\CoinPurchase;
use App\Models\Favorite;
use App\Models\Filter;
use App\Models\Gift;
use App\Models\Purchase;
use App\Models\SpecialRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    public function purchaseCoin(CoinPurchaseRequest $request): JsonResponse
    {
        $data = $request->validated();

        $coin = auth()->user()->coin;

        switch ($data['product_id']) {

            case 'flinzr_050_coins';
                $coin += 50;
                break;
            case 'flinzr_275_coins';
                $coin += 275;
                break;
            case 'flinzr_175_coins':
                $coin += 175;
                break;
            case 'flinzr_375_coins':
                $coin += 375;
                break;
            case 'flinzr_475_coins':
                $coin += 475;
                break;
            case 'flinzr_675_coins':
                $coin += 675;
                break;
        }

        try {
            DB::beginTransaction();

            auth()->user()->update([
                'coin' => $coin,
            ]);

            CoinPurchase::create([
                'user_id' => auth()->id(),
                'product_id' => $data['product_id'],
                'transaction_id' => $data['transaction_id'],
                'store' => $data['store'],
                'coins' => $coin,
            ]);

            DB::commit();

            return response()->json(['message' => 'Purchase successful']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function purchaseFilter(PurchaseFilterRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->filters->contains($request->filter_id)) {
            return response()->json(['message' => 'Filter already purchased'], 400);
        }

        DB::beginTransaction();
        try {
            $filter = Filter::findOrFail($request->filter_id);
            $filterType = $filter->collection->sales_type;
            $filterPrice = Price::Filter->getPrice();
            $artist = Filter::findOrFail($request->filter_id)->collection->user;
            $subscriptionValid = false;
            $purchase_date = null;

            if ($filterType === SalesType::Free) {
                return $this->handleFreeFilter($user, $filter, $artist);
            }

            $subscription = auth('sanctum')->user()->subscription;
            $customer_id = $subscription->data['customer_id'] ?? null;

            $commissionLevel = $artist->level;
            $percentage = $commissionLevel->getCommission();
            $earning = ($filterPrice / 25) * ($percentage / 100);


            if ($customer_id) {
                $response = SubscriptionController::fetchSubscriptionStatus($customer_id);
                if (isset($response['success']) && $response['success']) {
                    $product_identifier = $response['data']['subscriber']['entitlements']['flinzr_plus']['product_identifier'];
                    $data = $response['data']['subscriber']['subscriptions'][$product_identifier];
                    if ($data) {
                        $product_plan_identifier = $data['product_plan_identifier'];
                        $expires_date = $data['expires_date'];
                        $purchase_date = $data['purchase_date'];
                        $unsubscribe_detected_at = $data['unsubscribe_detected_at'];


                        if ($expires_date > now()) {

                            if ($product_plan_identifier == "monthly" || $product_plan_identifier == "yearly") {
                                $subscriptionValid = true;
                            }
                        }
                    }
                }
            }


            if ($filterType === SalesType::Subscription) {
                return $this->handleSubscriptionFilter($user, $filter, $filterPrice, $artist, $earning, $subscriptionValid, $purchase_date);
            }
            if ($filterType === SalesType::Paid) {
                return $this->handlePaidFilter($user, $filter, $filterPrice, $artist, $earning, $subscriptionValid, $purchase_date );
            }
            return response()->json(['message' => 'Filter purchase successful'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    private function handleFreeFilter($user, $filter, $artist)
    {
        $this->createPurchase($user, $filter->id, $artist, 0);
        $user->filters()->syncWithoutDetaching($filter->id);
        $this->handleReferralBonus($user);
        DB::commit();
        return response()->json(['message' => 'Filter purchased successfully']);
    }

    private function handleSubscriptionFilter($user, $filter, $filterPrice, $artist, $earning, $subscriptionValid, $purchase_date): JsonResponse
    {

        if ($subscriptionValid) {
            $subscriptionFiltersPurchaseCount = $this->getSubscriptionFiltersPurchaseCount($user, $purchase_date);
            if ($subscriptionFiltersPurchaseCount < 9) {
                $this->createPurchase($user, $filter->id, $artist, 0);
                $user->filters()->syncWithoutDetaching($filter->id);
                $this->handleReferralBonus($user);
                $artist->balance = $artist->balance + $earning;
                $artist->save();
                $this->updateArtistDetails($artist);
                return response()->json(['message' => 'Plus Filter purchased successfully']);
            }
        }


        if ($user->coin < $filterPrice) {
            return response()->json(['message' => 'Insufficient coin balance'], 400);
        }

        $user->decrement('coin', $filterPrice);
        $this->createPurchase($user, $filter->id, $artist, $filterPrice);
        $user->filters()->syncWithoutDetaching($filter->id);
        $this->handleReferralBonus($user);
        $artist->balance = $artist->balance + $earning;
        $artist->save();
        $this->updateArtistDetails($artist);
        DB::commit();

        return response()->json(['message' => 'Plus Filter purchased successfully']);
    }

    private function handlePaidFilter($user, $filter, $filterPrice, $artist, $earning, $subscriptionValid, $purchase_date): JsonResponse
    {

        if ($subscriptionValid) {
            $paidFiltersPurchaseCount = $this->getPaidFiltersPurchaseCount($user, $purchase_date);
            if ($paidFiltersPurchaseCount < 9) {
                $this->createPurchase($user, $filter->id, $artist, 0);
                $user->filters()->syncWithoutDetaching($filter->id);
                $this->handleReferralBonus($user);
                $artist->balance = $artist->balance + $earning;
                $artist->save();
                DB::commit();
                return response()->json(['message' => 'Paid Filter purchased successfully']);
            }
        }

        if ($user->coin < $filterPrice) {
            return response()->json(['message' => 'Insufficient coin balance'], 400);
        }

        $user->decrement('coin', $filterPrice);
        $this->createPurchase($user, $filter->id, $artist, $filterPrice);
        $user->filters()->syncWithoutDetaching($filter->id);
        $this->handleReferralBonus($user);
        $artist->balance = $artist->balance + $earning;
        $artist->save();
        DB::commit();

        return response()->json(['message' => 'Paid Filter purchased successfully']);
    }

    private function getPaidFiltersPurchaseCount($user, $createdAt): int
    {
        return Purchase::where('user_id', $user->id)
            ->where('created_at', '>', $createdAt)
            ->whereHas('filter.collection', function ($query) {
                $query->where('sales_type', 'paid');
            })->count();
    }

    private function getSubscriptionFiltersPurchaseCount($user, $createdAt): int
    {
        return Purchase::where('user_id', $user->id)
            ->where('created_at', '>', $createdAt)
            ->whereHas('filter.collection', function ($query) {
                $query->where('sales_type', 'subscription');
            })->count();
    }

    private function createPurchase($user, int $filterId, $artist, int $price): void
    {
        $commissionLevel = $artist->level;
        $percentage = $commissionLevel->getCommission();
        $earning = ($price / 25) * ($percentage / 100);

        Purchase::create([
            'user_id' => $user->id,
            'filter_id' => $filterId,
            'artist_id' => $artist->id,
            'earning' => $earning,
            'amount' => $price / 25,
        ]);

        // Update collection timestamp
        $collection = Filter::findOrFail($filterId)->collection;
        $collection->updated_at = now();
        $collection->save();
    }

    private function updateArtistDetails($artist): void
    {
        $downloadCount = Purchase::where('artist_id', $artist->id)->count();
        if ($downloadCount > CommissionLevel::Level7->getTarget()) {
            $artist->level = CommissionLevel::Level8;
        } elseif ($downloadCount > CommissionLevel::Level6->getTarget()) {
            $artist->level = CommissionLevel::Level7;
        } elseif ($downloadCount > CommissionLevel::Level5->getTarget()) {
            $artist->level = CommissionLevel::Level6;
        } elseif ($downloadCount > CommissionLevel::Level4->getTarget()) {
            $artist->level = CommissionLevel::Level5;
        } elseif ($downloadCount > CommissionLevel::Level3->getTarget()) {
            $artist->level = CommissionLevel::Level4;
        } elseif ($downloadCount > CommissionLevel::Level2->getTarget()) {
            $artist->level = CommissionLevel::Level3;
        } elseif ($downloadCount > CommissionLevel::Level1->getTarget()) {
            $artist->level = CommissionLevel::Level2;
        } else {
            $artist->level = CommissionLevel::Level1;
        }
        $artist->save();
    }

    private function handleReferralBonus($user): void
    {
        if ($user->purchases()->count() == 1) {
            $referredBy = $user->referred_by;
            if ($referredBy) {
                $referrer = User::where('referral_code', $referredBy)->first();
                if ($referrer) {
                    $referrer->increment('coin', 25);
                }
            }
        }
    }

    public function giftFilter(GiftFilterRequest $request): JsonResponse
    {
        $user = User::where('username', $request->username)->firstOrFail();

        if ($user->filters->pluck('id')->contains($request->filter_id)) {
            return response()->json(['message' => 'Filter already gifted'], 400);
        }

        $filter = Filter::findOrFail($request->filter_id);
        $filterPrice = Price::GiftFilter->getPrice();
        $artist = $filter->collection->user;
        $commissionLevel = $artist->level;
        $percentage = $commissionLevel->getCommission();
        $earning = ($filterPrice / 25) * ($percentage / 100);
        $sender = auth('sanctum')->user();
        $subscription = $sender->subscription;

        if ($subscription && isset($subscription->data['customer_id'])) {
            $customer_id = $subscription->data['customer_id'];

            if ($customer_id) {
                $response = SubscriptionController::fetchSubscriptionStatus($customer_id);
                if (isset($response['success']) && $response['success']) {
                    $product_identifier = $response['data']['subscriber']['entitlements']['flinzr_plus']['product_identifier'];
                    $data = $response['data']['subscriber']['subscriptions'][$product_identifier];
                    if ($data) {
                        $product_plan_identifier = $data['product_plan_identifier'];
                        $expires_date = $data['expires_date'];
                        $purchase_date = $data['purchase_date'];
                        $unsubscribe_detected_at = $data['unsubscribe_detected_at'];

                        if ($expires_date > now()) {
                            if ($product_plan_identifier == "monthly" || $product_plan_identifier == "yearly") {
                                $giftFilterCount = Gift::where('sender_id', auth()->id())->where('created_at', '>', $purchase_date)->count();
                                if ($giftFilterCount < 9) {
                                    return $this->createGift($user, $sender, $filter, $artist, $earning, $filterPrice);
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($sender->coin < $filterPrice) {
            return response()->json(['message' => 'Insufficient coin balance'], 400);
        }
        $sender->decrement('coin', $filterPrice);
        return $this->createGift($user, $sender, $filter, $artist, $earning, $filterPrice);

    }


    private function createGift($user, $sender, $filter, $artist, $earning, $filterPrice): JsonResponse
    {
        DB::beginTransaction();

        try {
            Gift::create([
                'user_id' => $user->id,
                'sender_id' => $sender->id,
                'filter_id' => $filter->id,
                'artist_id' => $artist->id,
                'earning' => $earning,
                'amount' => $filterPrice / 25,
            ]);

            $user->filters()->syncWithoutDetaching($filter->id);
            DB::commit();

            return response()->json(['message' => 'Gift successful']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gift process failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function monthlyUsedCounter()
    {
        $user = auth()->user();
        $subscription = auth('sanctum')->user()->subscription;
        $customer_id = $subscription->data['customer_id'] ?? null;
        if ($customer_id) {
            $response = SubscriptionController::fetchSubscriptionStatus($customer_id);
            if (isset($response['success']) && $response['success']) {
                $product_identifier = $response['data']['subscriber']['entitlements']['flinzr_plus']['product_identifier'];
                $data = $response['data']['subscriber']['subscriptions'][$product_identifier];
                if ($data) {
                    $product_plan_identifier = $data['product_plan_identifier'];
                    $expires_date = $data['expires_date'];
                    $purchase_date = $data['purchase_date'];
                    $unsubscribe_detected_at = $data['unsubscribe_detected_at'];


                    if ($expires_date > now()) {

                        if ($product_plan_identifier == "monthly" || $product_plan_identifier == "yearly") {
                            $plusFilter = Purchase::where('user_id', $user->id)
                                ->where('created_at', '>', $purchase_date)
                                ->whereHas('filter.collection', function ($query) {
                                    $query->where('sales_type', 'paid');
                                })->count();

                            $subscriptionFilter = Purchase::where('user_id', $user->id)->where('created_at', '>', $purchase_date)
                                ->whereHas('filter.collection', function ($query) {
                                    $query->where('sales_type', 'subscription');
                                })->count();

                            $giftFilter = Gift::where('sender_id', $user->id)->where('created_at', '>', $purchase_date)->count();
                            $coinDailyReward = null;
                            return response()->json([
                                'plus_filter' => $plusFilter,
                                'subscription_filter' => $subscriptionFilter,
                                'gift_filter' => $giftFilter,
                                'coin_daily_reward' => $coinDailyReward
                            ]);
                        }
                    }
                }
            }
        }
        return response()->json([
            'plus_filter' => null,
            'subscription_filter' => null,
            'gift_filter' => null,
            'coin_daily_reward' => null
        ]);
    }

    public function profileCounter(): JsonResponse
    {
        $purchaseCount = Purchase::where('user_id', auth()->id())->count();
        $giftCount = Gift::where('sender_id', auth()->id())->count();
        $favourites = Favorite::where('user_id', auth()->id())->count();
        $specialRequestCount = SpecialRequest::where('user_id', auth()->id())->whereNotNull('url')->count();


        return response()->json([
            'purchase_count' => $purchaseCount,
            'gift_count' => $giftCount,
            'favourites' => $favourites,
            'special_filters' => $specialRequestCount
        ]);
    }


    public function subscriptionFeature(): JsonResponse
    {
        $subscriptions = [
            [
                "type" => "monthly",
                "price" => 19.99,
                "features" => [
                    "9_plus_filters" => true,
                    "9_paid_filters" => true,
                    "9_gifts_to_friend" => true,
                    "50_percent_special_order" => false,
                    "2_coin_daily" => false,
                    "no_more_ads" => true,
                ],
            ],
            [
                "type" => "annual",
                "price" => 119.99,
                "monthly_equivalent" => 9.99,
                "features" => [
                    "9_plus_filters" => true,
                    "9_paid_filters" => true,
                    "9_gifts_to_friend" => true,
                    "50_percent_special_order" => true,
                    "2_coin_daily" => true,
                    "no_more_ads" => true,
                ],
            ],
        ];

        return response()->json($subscriptions);
    }

}
