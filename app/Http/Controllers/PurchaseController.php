<?php

namespace App\Http\Controllers;

use App\Enums\CommissionLevel;
use App\Enums\Price;
use App\Enums\SalesType;
use App\Http\Requests\CoinPurchaseRequest;
use App\Http\Requests\GiftFilterRequest;
use App\Http\Requests\PurchaseFilterRequest;
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

            auth()->user()->update(['coin' => $coin]);
            $data['coins'] = $coin;
            auth()->user()->coinPurchases()->create($data);

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
            $subscription = auth('sanctum')->user()->subscription;
            $customer_id = $subscription->data['customer_id'] ?? null;

            if (!$customer_id) {
                return response()->json(['message' => 'Invalid customer ID'], 400);
            }

            $response = SubscriptionController::fetchSubscriptionStatus($customer_id);
            if (!$response['success']) {
                return response()->json(['message' => 'Subscription validation failed'], 400);
            }

            $subscriptionData = $response['data'];
            $firstSubscription = $subscriptionData['items'][0] ?? null;

            if (!$firstSubscription || $firstSubscription['status'] !== 'active') {
                return response()->json(['message' => 'Subscription is not active'], 400);
            }

            $durationInDays = $this->getDurationInDays($firstSubscription);
            if ($durationInDays < 28 || $durationInDays > 375) {
                return response()->json(['message' => 'Invalid subscription duration'], 400);
            }

            $filterType = Filter::findOrFail($request->filter_id)->collection->sales_type;
            $filterPrice = Price::Filter->getPrice();
            $artist = Filter::findOrFail($request->filter_id)->collection->user;

            if ($filterType === SalesType::Paid) {
                $paidFiltersPurchaseCount = $this->getPaidFiltersPurchaseCount($user, $subscription->updated_at);
                if ($paidFiltersPurchaseCount > 9 && $user->coin < $filterPrice) {
                    return response()->json(['message' => 'Insufficient coin'], 400);
                }

                if ($paidFiltersPurchaseCount > 9) {
                    $user->decrement('coin', $filterPrice);
                }

                $this->createPurchase($user, $request->filter_id, $artist, $filterPrice);
                $this->updateArtistDetails($artist);
                $user->filters()->syncWithoutDetaching($request->filter_id);
                $this->handleReferralBonus($user);

                DB::commit();
                $purchaseType = $paidFiltersPurchaseCount > 9 ? 'Purchase successful' : 'Free Purchase successful';
                return response()->json(['message' => $purchaseType]);
            }

            if ($filterType === SalesType::Subscription) {
                $subscriptionFiltersPurchaseCount = $this->getSubscriptionFiltersPurchaseCount($user, $subscription->updated_at);
                if ($subscriptionFiltersPurchaseCount > 9) {
                    return response()->json(['message' => 'Subscription filter limit reached'], 400);
                }
                $this->createPurchase($user, $request->filter_id, $artist, $filterPrice);
                $this->updateArtistDetails($artist);
                $user->filters()->syncWithoutDetaching($request->filter_id);
                $this->handleReferralBonus($user);

                DB::commit();
                return response()->json(['message' => 'Free Purchase successful']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }


    private function getDurationInDays(array $subscription): float|int|null
    {
        $currentPeriodStartsAt = $subscription['current_period_starts_at'] ?? null;
        $currentPeriodEndsAt = $subscription['current_period_ends_at'] ?? null;

        if ($currentPeriodStartsAt && $currentPeriodEndsAt) {
            $startTimeSeconds = $currentPeriodStartsAt / 1000;
            $endTimeSeconds = $currentPeriodEndsAt / 1000;

            return ($endTimeSeconds - $startTimeSeconds) / 86400;
        }

        return null;
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

        $artist->increment('balance', $earning);
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
        $user = User::query()->where('username', $request->username)->first();
        if ($user->filters->pluck('id')->contains($request->filter_id)) {
            return response()->json(['message' => 'Filter already purchased'], 400);
        } else {
            $subscription = auth('sanctum')->user()->subscription;
            $customer_id = $subscription->data['customer_id'] ?? null;

            if (!$customer_id) {
                return response()->json(['message' => 'Invalid customer ID'], 400);
            }

            $response = SubscriptionController::fetchSubscriptionStatus($customer_id);
            if (!$response['success']) {
                return response()->json(['message' => 'Subscription validation failed'], 400);
            }

            $subscriptionData = $response['data'];
            $firstSubscription = $subscriptionData['items'][0] ?? null;

            if (!$firstSubscription || $firstSubscription['status'] !== 'active') {
                return response()->json(['message' => 'Subscription is not active'], 400);
            }

            $durationInDays = $this->getDurationInDays($firstSubscription);
            if ($durationInDays < 28 || $durationInDays > 375) {
                return response()->json(['message' => 'Invalid subscription duration'], 400);
            }
            $giftFilterCount = Gift::where('sender_id', auth()->id())->where('created_at', '>', $subscription->updated_at)->count();
            DB::beginTransaction();
            $sender = auth()->user();
            $coin = $sender->coin;
            $filterPrice = Price::GiftFilter->getPrice();
            if ($giftFilterCount > 9) {
                if ($sender->coin < $filterPrice) {
                    return response()->json(['message' => 'Insufficient coin'], 400);
                }
                $sender->decrement('coin', $filterPrice);
            }

            $artist = Filter::findOrFail($request->filter_id)->collection->user;
            $commissionLevel = $artist->level;
            $percentage = $commissionLevel->getCommission();
            $earning = (Price::GiftFilter->getPrice() / 25) * ($percentage / 100);
            Gift::create([
                'user_id' => $user->id,
                'sender_id' => $sender->id,
                'filter_id' => $request->filter_id,
                'artist_id' => $artist->id,
                'earning' => $earning,
                'amount' => Price::GiftFilter->getPrice() / 25,
            ]);
            $user->filters()->syncWithoutDetaching($request->filter_id);
            DB::commit();
            return response()->json(['message' => 'Gift successful']);
        }
    }

    public function monthlyUsedCounter()
    {
        $user = auth()->user();
        $subscription = auth('sanctum')->user()->subscription;
        $customer_id = $subscription->data['customer_id'] ?? null;

        if ($customer_id) {
            $response = SubscriptionController::fetchSubscriptionStatus($customer_id);
            if ($response['success']) {
                $subscriptionData = $response['data'];
                $firstSubscription = $subscriptionData['items'][0] ?? null;
                if ($firstSubscription || $firstSubscription['status'] == 'active') {
                    $durationInDays = $this->getDurationInDays($firstSubscription);
                    if ($durationInDays >= 28 && $durationInDays <= 375) {

                        $plusFilter = Purchase::where('user_id', $user->id)
                            ->where('created_at', '>', $subscription->updated_at)
                            ->whereHas('filter.collection', function ($query) {
                                $query->where('sales_type', 'paid');
                            })->count();

                        $subscriptionFilter = Purchase::where('user_id', $user->id)->where('created_at', '>', $subscription->updated_at)
                            ->whereHas('filter.collection', function ($query) {
                                $query->where('sales_type', 'subscription');
                            })->count();

                        $giftFilter = Gift::where('sender_id', $user->id)->where('created_at', '>', $subscription->updated_at)->count();
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

    public function profileCounter()
    {
        $purchaseCount = Purchase::where('user_id', auth()->id())->count();
        $giftCount = Gift::where('sender_id', auth()->id())->count();
        $favourites = Favorite::where('user_id', auth()->id())->count();
        $specialRequestCount = SpecialRequest::where('user_id', auth()->id())->count();


        return response()->json([
            'purchase_count' => $purchaseCount,
            'gift_count' => $giftCount,
            'favourites' => $favourites,
            'special_filters' => $specialRequestCount
        ]);
    }


}
