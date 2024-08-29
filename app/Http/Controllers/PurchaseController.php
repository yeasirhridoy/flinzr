<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseFilterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function purchaseFilter(PurchaseFilterRequest $request): JsonResponse
    {
        auth()->user()->filters()->syncWithoutDetaching($request->filter_id);
        return response()->json(['message' => 'Purchase successful']);
    }
}
