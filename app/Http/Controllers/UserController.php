<?php

namespace App\Http\Controllers;

use App\Enums\Price;
use App\Enums\UserType;
use App\Http\Requests\ArtistRequestRequest;
use App\Http\Resources\ArtistRequestResource;
use App\Http\Resources\MinimumUserResource;
use App\Models\ArtistRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function settings(): JsonResponse
    {
        $prices = [];
        foreach (Price::cases() as $price){
            $prices[$price->value] = $price->getPrice();
        }
        return response()->json($prices);
    }
    /**
     * Display a listing of the resource.
     */
    public function artists(): AnonymousResourceCollection
    {
        $users = User::query()->withCount('followers','followings')->where('type', UserType::Artist)->get();

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

    public function myArtistRequest(): JsonResponse|ArtistRequestResource
    {
        $artistRequest = ArtistRequest::query()->where('user_id', auth()->id())->where('created_at','>',now()->subMonth())->first();

        if ($artistRequest){
            return new ArtistRequestResource($artistRequest);
        } else{
            return response()->json(['message' => 'No request in last 30 days']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
