<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'data' => 'required|array',
            'data.customer_id' => 'required|string',
            'ends_at' => 'nullable|date',
        ];
        $request->validate($rules);

        $data = $request->all();
        $endDate = SubscriptionController::checkSubscriptionValidity(auth()->user()->username);
        if ($endDate){
            $data['ends_at'] = Carbon::parse($endDate);
        }

        $subscription = auth('sanctum')->user()->subscription;
        if ($subscription) {
            $subscription->update($data);
        } else {
            auth('sanctum')->user()->subscription()->create($data);
        }

        return response()->json(['message' => 'Subscription updated successfully']);
    }

    public function dailyCoin(): JsonResponse
    {
        $subscription = auth('sanctum')->user()->subscription;
        $customer_id = $subscription->data['customer_id'] ?? null;

        $cacheKey = 'daily-coin-claim-' . auth('sanctum')->id() . '-' . now()->format('Y-m-d');

        if (cache()->has($cacheKey)) {
            return response()->json(['message' => 'You have already claimed your daily coins'], 400);
        }

        if ($customer_id) {
            $response = $this->fetchSubscriptionStatus($customer_id);

            if (isset($response['success']) && $response['success']) {

                $product_identifier = $response['data']['subscriber']['entitlements']['flinzr_plus']['product_identifier'];
                $data = $response['data']['subscriber']['subscriptions'][$product_identifier];
                if ($data) {
                    $product_plan_identifier = $data['product_plan_identifier'];
                    $expires_date = $data['expires_date'];
                    $this->updateSubscription($subscription, $expires_date, $data);

                    if (Carbon::parse($expires_date)->isFuture()) {
                        if ($product_plan_identifier == "yearly") {
                            auth('sanctum')->user()->increment('coin', 10);
                            // cache()->put($cacheKey, true, now()->addDay());
                            cache()->put($cacheKey, true, now()->addMinutes(5));
                            return response()->json(['message' => 'Coins added successfully']);
                        }
                    } else {
                        return response()->json(['message' => 'You are not subscribed to claim daily coins'], 403);
                    }
                }
            }
        }
        return response()->json(['message' => 'You are not subscribed to claim daily coins'], 403);


    }


    public static function fetchSubscriptionStatus(string $customerId): array
    {
        $baseUrl = config('app.revenuecat_url');
        $url = $baseUrl . "/subscribers/" . $customerId;
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
    }

    private function updateSubscription($subscription, $expires_date, $data): void
    {
        if (Carbon::parse($expires_date)->isFuture()) {
            $status = true;
        } else {
            $status = false;
        }
            $subscription->is_active = $status;
            $subscription->ends_at = $expires_date;
            $subscription->save();
    }


    public static function checkSubscription(): string|null
    {
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

                    if (Carbon::parse($expires_date)->greaterThan(now())) {
                       return $product_plan_identifier;
                    }
                }
            }
        }

        return null;
    }

    public static function checkSubscriptionValidity($username)
    {
            $response = SubscriptionController::fetchSubscriptionStatus($username);
            if (isset($response['success']) && $response['success']) {
                $product_identifier = $response['data']['subscriber']['entitlements']['flinzr_plus']['product_identifier'];
                $data = $response['data']['subscriber']['subscriptions'][$product_identifier];
                if ($data) {
                    return $data['expires_date'];
                }
            }
            return null;
    }


}
