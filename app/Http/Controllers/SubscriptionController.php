<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'data' => 'nullable|array',
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
        $cacheKey = 'daily-coin-claim-' . auth('sanctum')->id() . '-' . now()->format('Y-m-d');
        if ($subscription && ($subscription->ends_at >= now() || $subscription->ends_at == null)) {
            if (!cache()->has($cacheKey)) {
                auth('sanctum')->user()->increment('coin', 10);
                cache()->put($cacheKey, true, now()->addDay());
                return response()->json(['message' => 'Coins added successfully']);
            } else {
                return response()->json(['message' => 'You have already claimed your daily coins']);
            }
        } else {
            return response()->json(['message' => 'You are not subscribed to claim daily coins']);
        }
    }
}
