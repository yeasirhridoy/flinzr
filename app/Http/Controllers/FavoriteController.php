<?php

namespace App\Http\Controllers;

use App\Enums\PlatformType;
use App\Http\Requests\FavoriteStoreRequest;
use App\Http\Resources\CollectionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        request()->validate([
            'type' => 'in:snapchat,instagram,tiktok',
        ]);

        $query = auth()->user()->favoriteCollections()->active()->with(['user','filters','colors']);

        if ($type = request()->input('type')) {
            $query->where('type', PlatformType::tryFrom($type));
        }

        $collections = $query->get();

        $purchasedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->purchases()->pluck('filter_id') : collect();
        $giftedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->gifts()->pluck('filter_id') : collect();

        $collections = $collections->map(function ($collection) use ($purchasedFilters, $giftedFilters) {
            $collection->is_favorite = true;
            $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
                $filter->is_purchased = $purchasedFilters->contains($filter->id);
                $filter->is_gifted = $giftedFilters->contains($filter->id);
                return $filter;
            });
            return $collection;
        });

        return CollectionResource::collection($collections);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(FavoriteStoreRequest $request): JsonResponse
    {
        $favorite = $request->user()->favorites()->where('collection_id', $request->collection_id)->first();
        if ($favorite) {
            $favorite->delete();
            return response()->json(['message' => 'Filter removed from favorites']);
        } else {
            $request->user()->favorites()->create($request->validated());
            return response()->json(['message' => 'Filter added to favorites']);
        }
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
