<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Http\Requests\InfluencerRequestRequest;
use App\Http\Requests\SpecialRequestRequest;
use App\Http\Resources\SpecialRequestResource;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RequestController extends Controller
{
    public function storeSpecialRequest(SpecialRequestRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['status'] = RequestStatus::Pending;

        if (isset($data['image'])) {
            $imageData = $data['image'];
            $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageData));

            $imageName = Str::random(32);

            $s3Path = 'special-requests/' . $imageName;
            Storage::put($s3Path, $image,'public');

            $data['image'] = $s3Path;
        }

        $specialRequest = $request->user()->specialRequests()->create($data);
        return new SpecialRequestResource($specialRequest);
    }
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

    public function storePayoutRequest(Request $request): JsonResponse
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

        if ($request->user()->balance < 50) {
            return response()->json(['message' => 'You need minimum $50 for payout request.'], 400);
        }

        $payoutRequest = $request->user()->payoutRequest;

        if ($payoutRequest && ($payoutRequest->status !== RequestStatus::Complete || $payoutRequest->status !== RequestStatus::Cancelled)) {
            return response()->json(['message' => 'You have already requested for payout.'], 400);
        }

        if ($payoutRequest) {
            $payoutRequest->update($data);
        } else {
            $payoutRequest = $request->user()->payoutRequest()->create($data);
        }
        return response()->json($payoutRequest->fresh());
    }
}
