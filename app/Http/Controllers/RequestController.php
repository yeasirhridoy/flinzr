<?php

namespace App\Http\Controllers;

use App\Http\Requests\CollectionRequestRequest;
use App\Http\Requests\InfluencerRequestRequest;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function getInfluencerRequest(): JsonResponse
    {
        return response()->json(auth()->user()->influencerRequest);
    }

    public function storeInfluencerRequest(InfluencerRequestRequest $request): JsonResponse
    {
        $influencerRequest = $request->user()->influencerRequest;
        $data = $request->validated();
        $countryCode = $data['country_code'];
        $data['country_id'] = Country::where('code', $countryCode)->first()->id;
        unset($data['country_code']);
        if (!$influencerRequest) {
            $influencerRequest = $request->user()->influencerRequest()->create($data);
        } else {
            $influencerRequest->update($data);
        }
        return response()->json($influencerRequest);
    }

    public function storePayoutRequest(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string'],
            'id_no' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'country_code' => ['required', 'string', 'exists:countries,code'],
        ]);
        $data = $request->all();
        $data['country_id'] = Country::where('code', $data['country_code'])->first()->id;
        unset($data['country_code']);
        $payoutRequest = $request->user()->payoutRequest;

        if ($payoutRequest) {
            $payoutRequest->update($data);
        } else {
            $payoutRequest = $request->user()->payoutRequest()->create($data);
        }
        return response()->json($payoutRequest->fresh());
    }
}
