<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Requests\ArtistRequestRequest;
use App\Http\Resources\ArtistRequestResource;
use App\Http\Resources\MinimumUserResource;
use App\Models\ArtistRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function artists(): AnonymousResourceCollection
    {
        $users = User::query()->where('type', UserType::Artist)->paginate();

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

    public function myArtistRequest(): ArtistRequestResource
    {
        $artistRequest = ArtistRequest::query()->where('user_id', auth()->id())->first();

        return new ArtistRequestResource($artistRequest);
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
