<?php

namespace App\Http\Controllers;

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
        $collections = auth()->user()->favoriteCollections()->active()->with('user')->paginate()->through(function ($collection) {
            $collection->is_favorite = true;
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
