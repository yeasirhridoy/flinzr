<?php

namespace App\Http\Controllers;

use App\Enums\PlatformType;
use App\Http\Requests\CollectionStoreRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $rules = [
            'type' => [Rule::in(PlatformType::values())],
            'category_id' => 'exists:categories,id',
            'user_id' => 'exists:users,id',
            'featured' => 'in:true',
            'query' => 'string',
            'tags' => 'string|regex:/^[0-9,]+$/',
            'colors' => 'string|regex:/^[0-9,]+$/',
        ];

        request()->validate($rules);

        $collections = Collection::query()
            ->where('is_active', true)
            ->with('user')
            ->orderBy('order_column');

        if (request()->filled('type')) {
            $collections->where('type', request('type'));
        }

        if (request()->filled('category_id')) {
            $collections->where('category_id', request('category_id'));
        }

        if (request()->filled('user_id')) {
            $collections->where('user_id', request('user_id'));
        }

        if (request()->filled('featured') && request('featured') === 'true') {
            $collections->where('is_featured', true);
        }

        if (request()->filled('query')) {
            $collections->where(function ($query) {
                $query->where('eng_name', 'like', '%' . request('query') . '%')
                    ->orWhere('arabic_name', 'like', '%' . request('query') . '%');
            });
        }

        if (request()->filled('tags')) {
            $collections->whereHas('tags', function ($query) {
                $query->whereIn('tag_id', explode(',',request('tags')));
            });
        }

        if (request()->filled('colors')) {
            $collections->whereHas('colors', function ($query) {
                $query->whereIn('color_id', explode(',', request('colors')));
            });
        }

        if (auth('sanctum')->check() && auth('sanctum')->user()->country_id) {
            $collections->where(function ($query) {
                $query->whereHas('regions', function ($query) {
                    $query->whereHas('countries', function ($query) {
                        $query->where('country_id', auth('sanctum')->user()->country_id);
                    });
                })->orWhereDoesntHave('regions');
            });
        }
        $favoriteCollections = auth('sanctum')->check() ? auth('sanctum')->user()->favoriteCollections()->pluck('collection_id') : collect();
        $collections = $collections->paginate()->through(function ($collection) use ($favoriteCollections) {
            $collection->is_favorite = $favoriteCollections->contains($collection->id);
            return $collection;
        });

        return CollectionResource::collection($collections);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CollectionStoreRequest $request): CollectionResource
    {
        $data = $request->validated();
        $filtersData = $data['filters'];
        $collectionData = collect($data)->except('filters')->toArray();
        $collection = Collection::create($collectionData);
        $collection->filters()->createMany($filtersData);
        return new CollectionResource($collection->load('filters'));
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
