<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowRequest;
use App\Http\Resources\MinimumUserResource;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FollowController extends Controller
{
    public function toggleFollow(FollowRequest $request): JsonResponse
    {
        $follow = Follow::query()
            ->where('follower_id', auth()->id())
            ->where('followee_id', $request->followee_id)
            ->first();

        if ($follow) {
            $follow->delete();
            return response()->json(['message' => 'User unfollowed']);
        } else {
            Follow::create([
                'follower_id' => auth()->id(),
                'followee_id' => $request->followee_id
            ]);
            return response()->json(['message' => 'User followed']);
        }

    }

    public function followers(): AnonymousResourceCollection
    {
        return MinimumUserResource::collection(auth()->user()->followers);
    }

    public function followings(): AnonymousResourceCollection
    {
        return MinimumUserResource::collection(auth()->user()->followings);
    }
}
