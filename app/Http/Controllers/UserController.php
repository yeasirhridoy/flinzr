<?php

namespace App\Http\Controllers;

use App\Enums\Price;
use App\Enums\RequestStatus;
use App\Enums\UserType;
use App\Http\Requests\ArtistRequestRequest;
use App\Http\Resources\ArtistRequestResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\MinimumUserResource;
use App\Http\Resources\PayoutRequestResource;
use App\Models\ArtistRequest;
use App\Models\InfluencerRequest;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function settings(): JsonResponse
    {
        $prices = [];
        foreach (Price::cases() as $price) {
            $prices[$price->value] = $price->getPrice();
        }
        return response()->json($prices);
    }

    /**
     * Display a listing of the resource.
     */
    public function artists(): AnonymousResourceCollection
    {
        $users = User::query()->withCount('followers', 'followings')->where('type', UserType::Artist)->get();

        $users->map(function ($user) {
            $user->is_following = $user->followers->contains('id', auth('sanctum')->id());
            $user->download = Purchase::query()->where('artist_id', $user->id)->count();
            $user->next_level_target  = $user->level->getTarget();
            $user->percent_completed = (float) number_format($user->download/$user->level->getTarget() * 100,2);


            return $user;
        });

        return MinimumUserResource::collection($users);
    }

    public function artistRequest(ArtistRequestRequest $request): ArtistRequestResource
    {
        $artistRequest = ArtistRequest::firstOrCreate([
            'user_id' => auth()->id()
        ],
            $request->validated()
        );

        return new ArtistRequestResource($artistRequest);
    }

    public function myArtistRequest(): JsonResponse
    {
        $artistRequest = ArtistRequest::query()->where('user_id', auth('sanctum')->id())->where('created_at', '>', now()->subMonth())->latest()->first();
        $influencerRequest = InfluencerRequest::query()->where('user_id', auth('sanctum')->id())->where('created_at', '>', now()->subMonth())->latest()->first();

        if ($artistRequest) {
            $artistResponse = [
                'requested' => true,
                'status' => $artistRequest->status->getLabel(),
                'message' => 'Requested in last 30 days'
            ];
        } else {
            $artistResponse = [
                'requested' => false,
                'status' => null,
                'message' => 'No request in last 30 days'
            ];
        }

        if ($influencerRequest) {
            $influencerResponse = [
                'requested' => true,
                'status' => $influencerRequest->status->getLabel(),
                'message' => 'Requested in last 30 days'
            ];
        } else {
            $influencerResponse = [
                'requested' => false,
                'status' => null,
                'message' => 'No request in last 30 days'
            ];
        }

        return response()->json([
            'artist' => $artistResponse,
            'influencer' => $influencerResponse
        ]);
    }

    public function artistSetting(): JsonResponse
    {
        $user = auth('sanctum')->user();
        $data = [];
        $pendingBalance = $user->payoutRequests()->where('status', RequestStatus::Pending)->sum('amount') / 100;
        $userBalance = $user->balance;
        $data['balance'] = number_format($userBalance - $pendingBalance,2);
        $data['pending_balance'] = number_format($pendingBalance,2);
        $data['level'] = auth('sanctum')->user()->level;
        $data['earnings'] = number_format(Purchase::query()->where('artist_id', auth('sanctum')->id())->sum('earning') / 100,2);
        $data['downloads'] = Purchase::query()->where('artist_id', auth('sanctum')->id())->count();
        $data['next_level_target'] = auth('sanctum')->user()->level->getTarget();
        $data['percent_completed'] = (float) number_format($data['downloads']/auth('sanctum')->user()->level->getTarget() * 100,2);
        $data['payout_requests'] = PayoutRequestResource::collection(auth('sanctum')->user()->payoutRequests()->latest()->get());
        $data['upload_requests'] = CountryResource::collection(auth('sanctum')->user()->collections()->latest()->get());
        $data['uploaded_collections'] =  CollectionResource::collection(auth('sanctum')->user()->collections()->with(['filters'])->orderBy('id', 'DESC')->get());
        $data['payout_method'] = $user->payoutMethod;

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function artistFollow(Request $request): JsonResponse
    {
        $user= User::query()->findOrFail($request->user_id);
        $data['followers'] = MinimumUserResource::collection($user->followers);
        $data['followings'] = MinimumUserResource::collection($user->followings);
        return response()->json($data);
    }



}
