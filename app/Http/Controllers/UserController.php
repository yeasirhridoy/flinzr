<?php

namespace App\Http\Controllers;

use App\Enums\Price;
use App\Enums\UserType;
use App\Http\Requests\ArtistRequestRequest;
use App\Http\Resources\ArtistRequestResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\MinimumUserResource;
use App\Http\Resources\PayoutRequestResource;
use App\Models\ArtistRequest;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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

        if ($artistRequest) {
            return response()->json([
                'requested' => true,
                'status' => $artistRequest->status->getLabel(),
                'message' => 'Requested in last 30 days'
            ]);
        } else {
            return response()->json([
                'requested' => false,
                'status' => null,
                'message' => 'No request in last 30 days'
            ]);
        }
    }

    public function artistSetting(): JsonResponse
    {
        $data = [];
        $data['level'] = auth('sanctum')->user()->level;
        $data['earnings'] = Purchase::query()->where('artist_id', auth('sanctum')->id())->sum('earning');
        $data['downloads'] = Purchase::query()->where('artist_id', auth('sanctum')->id())->count();
        $data['next_level_target'] = auth('sanctum')->user()->level->getTarget();
        $data['percent_completed'] = number_format($data['downloads']/auth('sanctum')->user()->level->getTarget() * 100,2);
        $data['payout_request'] = PayoutRequestResource::collection(auth('sanctum')->user()->payoutRequest()->latest()->get());
        $data['upload_requests'] = CountryResource::collection(auth('sanctum')->user()->collections()->latest()->get());

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
}
