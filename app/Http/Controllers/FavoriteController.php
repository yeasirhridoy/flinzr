<?php

namespace App\Http\Controllers;

use App\Enums\PlatformType;
use App\Http\Requests\FavoriteStoreRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

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

        $query = auth()->user()->favoriteCollections()->active()->with(['user','filters','colors'])->addSelect(['add_favorite_at' => Favorite::select('created_at')
            ->whereColumn('favorites.collection_id', 'collections.id')
            ->where('favorites.user_id', auth()->id())
        ]);

        if ($type = request()->input('type')) {
            $query->where('type', PlatformType::tryFrom($type));
        }

        $collections = $query->get();


        $purchasedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->purchases()->get(['filter_id', 'created_at'])
            ->mapWithKeys(fn($purchase) => [$purchase->filter_id => $purchase->created_at]) : collect();
        $giftedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->gifts()->get(['filter_id', 'created_at'])
            ->mapWithKeys(fn($gift) => [$gift->filter_id => $gift->created_at]) : collect();

        $collections = $collections->map(function ($collection) use ($purchasedFilters, $giftedFilters) {
            $collection->is_favorite = true;
            $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
                $filter->is_purchased = $purchasedFilters->has($filter->id);
                $filter->is_gifted = $giftedFilters->has($filter->id);

                $filter->purchased_at = $purchasedFilters->get($filter->id, null);
                $filter->gifted_at = $giftedFilters->get($filter->id, null);
                return $filter;
            });
            return $collection;
        });
        $collections = $collections->sortByDesc('add_favorite_at');
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
