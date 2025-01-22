<?php

namespace App\Http\Controllers;

use App\Enums\Price;
use App\Enums\RequestStatus;
use App\Http\Requests\InfluencerRequestRequest;
use App\Http\Requests\SpecialRequestRequest;
use App\Http\Resources\SpecialRequestResource;
use App\Models\Country;
use App\Models\PayoutMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RequestController extends Controller
{
    public function storeSpecialRequest(SpecialRequestRequest $request): SpecialRequestResource|JsonResponse
    {
        $data = $request->validated();

        $user = $request->user();
        $duration = SubscriptionController::checkSubscription();

        if ($duration && $duration >= 355 && $duration <= 375) {
            $price = Price::SpecialFilter->getPrice() / 2;
        } else {
            $price = Price::SpecialFilter->getPrice();
        }

        $coin = $user->coin;
        if ($coin < $price) {
            return response()->json(['message' => 'Insufficient coin'], 400);
        }

        $data['user_id'] = $user->id;
        $data['status'] = RequestStatus::Pending;

        if (isset($data['image'])) {
            $imageData = $data['image'];
            $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageData));

            $imageName = Str::random(32);

            $s3Path = 'special-requests/' . $imageName;
            Storage::put($s3Path, $image, 'public');

            $data['image'] = $s3Path;
        }

        DB::beginTransaction();
        try {
            $specialRequest = $request->user()->specialRequests()->create($data);
            $user->update(['coin' => $coin - $price]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong'], 500);
        }
        return new SpecialRequestResource($specialRequest);
    }

    public function getSpecialRequest(): AnonymousResourceCollection
    {
        return SpecialRequestResource::collection(auth()->user()->specialRequests->sortByDesc('id'));
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
//        $request->validate([
//            'full_name' => ['required', 'string'],
//            'id_no' => ['required', 'string'],
//            'phone' => ['required', 'string'],
//            'country_code' => ['required', 'string', 'exists:countries,code'],
//        ]);
//        $data = $request->all();
//        $data['country_id'] = Country::where('code', $data['country_code'])->first()->id;
//        unset($data['country_code']);

//        if ($request->user()->balance < 50) {
//            return response()->json(['message' => 'You need minimum $50 for payout request.'], 400);
//        }
        $rules = [
            'update' => 'required|boolean',
        ];

        $request->validate($rules);

        if($request->update == true) {
            $rules = [
                'country_code' => 'required|exists:countries,code',
                'full_name' => 'required|string',
                'id_no' => 'required|string',
                'phone' => 'required|string',
            ];

            $request->validate($rules);

            $payoutMethod = PayoutMethod::query()->updateOrCreate([
                'user_id' => auth()->id(),
            ], [
                'country_id' => Country::where('code', $request->country_code)->first()->id,
                'full_name' => $request->full_name,
                'id_no' => $request->id_no,
                'phone' => $request->phone,
            ]);

            if (!$payoutMethod->exists) {
                return response()->json(['message' => 'Failed to execute query'], 500);
            }
        }

        if ($request->user()->payoutRequests()->where('status', RequestStatus::Pending)->exists()) {
            return response()->json(['message' => 'You have already requested for payout.'], 400);
        }

        $data['amount'] = $request->user()->balance;
        $payoutRequest = $request->user()->payoutRequests()->create($data);
        return response()->json($payoutRequest->fresh());
    }
}
