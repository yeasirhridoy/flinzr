<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function store(Request $request)
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
}
