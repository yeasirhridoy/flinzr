<?php

namespace App\Http\Controllers;

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

        $subscription = auth('sanctum')->user()->subscription;
        if ($subscription) {
            $subscription->update($request->all());
        } else {
            auth('sanctum')->user()->subscription()->create($request->all());
        }

        return response()->json(['message' => 'Subscription updated successfully']);
    }

    public function dailyCoin(): JsonResponse
    {
        $subscription = auth('sanctum')->user()->subscription;
        $customer_id = $subscription->data['customer_id'] ?? null;
        $cacheKey = 'daily-coin-claim-' . auth('sanctum')->id() . '-' . now()->format('Y-m-d');
        if (!$subscription || ($subscription->ends_at < now() && $subscription->ends_at !== null)) {
            return response()->json(['message' => 'You are not subscribed to claim daily coins'], 403);
        }

        if (cache()->has($cacheKey)) {
            return response()->json(['message' => 'You have already claimed your daily coins'], 400);
        }
        $response = $this->fetchSubscriptionStatus($customer_id);
        if ($response['success']) {
            $subscriptionData = $response['data'];
            $firstSubscription = $subscriptionData['items'][0] ?? null;
            $this->updateSubscription($subscription, $firstSubscription);

            if ($firstSubscription && $firstSubscription['status'] === 'active') {
                auth('sanctum')->user()->increment('coin', 10);
                cache()->put($cacheKey, true, now()->addDay());
                return response()->json(['message' => 'Coins added successfully']);
            } else {
                return response()->json(['message' => 'You are not subscribed to claim daily coins'], 403);
            }
        }else{
            return response()->json(['message' => 'You are not subscribed to claim daily coins'], 403);
        }
    }


    private function fetchSubscriptionStatus(string $customerId): array
    {
        $projectId = config('app.revenuecat_project_id');
        $baseUrl = config('app.revenuecat_url');
        $url = $baseUrl . "/projects/{$projectId}/customers/{$customerId}/subscriptions";
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

    private function updateSubscription($subscription, array $firstSubscription): void
    {
        $currentPeriodEndsAt = $firstSubscription['current_period_ends_at'] ?? null;
        $currentPeriodEndsAtDate = $currentPeriodEndsAt
            ? date('Y-m-d H:i:s', $currentPeriodEndsAt / 1000)
            : null;

        $subscription->data = $firstSubscription;
        $subscription->is_active = $firstSubscription['status'] === 'active' ? 1 : 0;
        $subscription->ends_at = $currentPeriodEndsAtDate;
        $subscription->save();
    }
}
