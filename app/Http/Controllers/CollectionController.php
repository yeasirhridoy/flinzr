<?php

namespace App\Http\Controllers;

use App\Enums\PlatformType;
use App\Enums\SalesType;
use App\Http\Requests\CollectionStoreRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function purchasedCollections(): AnonymousResourceCollection
    {
        request()->validate([
            'type' => 'in:snapchat,instagram,tiktok',
        ]);

        $purchasedCollections = Collection::with(['user', 'filters', 'colors'])->whereHas('filters', function ($query) {
            $query->whereIn('id', auth('sanctum')->user()->purchases()->pluck('filter_id'));
        });

        if (request()->filled('type')) {
            $purchasedCollections->where('type', PlatformType::tryFrom(request('type')));
        }

        $purchasedCollections = $purchasedCollections->get();

        $favoriteCollections = auth('sanctum')->check() ? auth('sanctum')->user()->favoriteCollections()->pluck('collection_id') : collect();
        $purchasedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->purchases()->pluck('filter_id') : collect();
        $giftedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->gifts()->pluck('filter_id') : collect();

        $purchasedCollections->map(function ($collection) use ($giftedFilters, $purchasedFilters, $favoriteCollections) {
            $collection->is_favorite = $favoriteCollections->contains($collection->id);
            $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
                $filter->is_purchased = $purchasedFilters->contains($filter->id);
                $filter->is_gifted = $giftedFilters->contains($filter->id);
                return $filter;
            });
            return $collection;
        });

        return CollectionResource::collection($purchasedCollections);
    }

    public function giftedCollections(): AnonymousResourceCollection
    {
        $giftedCollections = Collection::with(['user', 'filters', 'colors'])->whereHas('filters', function ($query) {
            $query->whereIn('id', auth('sanctum')->user()->gifts()->pluck('filter_id'));
        })->get();

        $favoriteCollections = auth('sanctum')->check() ? auth('sanctum')->user()->favoriteCollections()->pluck('collection_id') : collect();
        $purchasedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->purchases()->pluck('filter_id') : collect();
        $giftedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->gifts()->pluck('filter_id') : collect();

        $giftedCollections->map(function ($collection) use ($giftedFilters, $purchasedFilters, $favoriteCollections) {
            $collection->is_favorite = $favoriteCollections->contains($collection->id);
            $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
                $filter->is_purchased = $purchasedFilters->contains($filter->id);
                $filter->is_gifted = $giftedFilters->contains($filter->id);
                return $filter;
            });
            return $collection;
        });

        return CollectionResource::collection($giftedCollections);
    }

    public function explore(): JsonResponse
    {
        $rules = [
            'country_code' => 'required|exists:countries,code',
        ];

        request()->validate($rules);

        $countryCode = request('country_code');
        $countryId = Country::where('code', $countryCode)->first()->id;

        $collectionsQuery = Collection::query()
            ->where(function ($query) use ($countryId) {
                $query->whereHas('regions', function ($query) use ($countryId) {
                    $query->whereHas('countries', function ($query) use ($countryId) {
                        $query->where('country_id', $countryId);
                    });
                })->orWhereDoesntHave('regions');
            })
            ->with(['user', 'filters', 'colors']);

        if (request()->filled('type')) {
            $collectionsQuery->where('type', request('type'));
        }

        $favoriteCollections = auth('sanctum')->check() ? auth('sanctum')->user()->favoriteCollections()->pluck('collection_id') : collect();
        $purchasedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->purchases()->pluck('filter_id') : collect();
        $giftedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->gifts()->pluck('filter_id') : collect();

        $trendingCollections = $collectionsQuery->where('is_trending', true)->get()->map(function ($collection) use ($giftedFilters, $purchasedFilters, $favoriteCollections) {
            $collection->is_favorite = $favoriteCollections->contains($collection->id);
            $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
                $filter->is_purchased = $purchasedFilters->contains($filter->id);
                $filter->is_gifted = $giftedFilters->contains($filter->id);
                return $filter;
            });
            return $collection;
        });
        $featuredCollections = $collectionsQuery->where('is_featured', true)->get()->map(function ($collection) use ($giftedFilters, $purchasedFilters, $favoriteCollections) {
            $collection->is_favorite = $favoriteCollections->contains($collection->id);
            $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
                $filter->is_purchased = $purchasedFilters->contains($filter->id);
                $filter->is_gifted = $giftedFilters->contains($filter->id);
                return $filter;
            });
            return $collection;
        });

        return response()->json([
            'trending' => CollectionResource::collection($trendingCollections),
            'featured' => CollectionResource::collection($featuredCollections),
        ]);
    }

    public function myCollections(): AnonymousResourceCollection
    {
        $collections = auth('sanctum')->user()->collections()->with(['filters'])->get();
        return CollectionResource::collection($collections);
    }

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
            'sales_type' => [Rule::in(SalesType::values())],
        ];

        request()->validate($rules);

        $collections = Collection::query()
            ->where('is_active', true)
            ->with(['user', 'filters', 'colors'])
            ->orderBy('order_column');

        if (request()->filled('type')) {
            $collections->where('type', request('type'));
        } else {
            $collections->whereNot('type', PlatformType::Banner);
        }

        if (request()->filled('sales_type')) {
            $collections->where('sales_type', request('sales_type'));
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
                $query->whereIn('tag_id', explode(',', request('tags')));
            });
        }

        if (request()->filled('colors')) {
            $collections->whereHas('colors', function ($query) {
                $query->whereIn('color_id', explode(',', request('colors')));
            });
        }

        if ((auth('sanctum')->check() && auth('sanctum')->user()->country_id) || request()->filled('country_id') || request()->filled('country_code')) {
            if (\request()->filled('country_code')) {
                $countryId = Country::where('code', request('country_code'))->first()->id;
            } else {
                $countryId = auth('sanctum')->check() ? auth('sanctum')->user()->country_id : request('country_id');
            }
            $collections->where(function ($query) use ($countryId) {
                $query->whereHas('regions', function ($query) use ($countryId) {
                    $query->whereHas('countries', function ($query) use ($countryId) {
                        $query->where('country_id', $countryId);
                    });
                })->orWhereDoesntHave('regions');
            });
        }

        $favoriteCollections = auth('sanctum')->check() ? auth('sanctum')->user()->favoriteCollections()->pluck('collection_id') : collect();
        $purchasedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->purchases()->pluck('filter_id') : collect();
        $giftedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->gifts()->pluck('filter_id') : collect();

        if (\request('type') === PlatformType::Banner->value) {
            $collections = $collections->get()->map(function ($collection) use ($giftedFilters, $purchasedFilters, $favoriteCollections) {
                $collection->is_favorite = $favoriteCollections->contains($collection->id);
                $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
                    $filter->is_purchased = $purchasedFilters->contains($filter->id);
                    $filter->is_gifted = $giftedFilters->contains($filter->id);
                    return $filter;
                });
                return $collection;
            });
        } else {
            $collections = $collections->paginate(10)->through(function ($collection) use ($giftedFilters, $purchasedFilters, $favoriteCollections) {
                $collection->is_favorite = $favoriteCollections->contains($collection->id);
                $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
                    $filter->is_purchased = $purchasedFilters->contains($filter->id);
                    $filter->is_gifted = $giftedFilters->contains($filter->id);
                    return $filter;
                });
                return $collection;
            });
        }

        return CollectionResource::collection($collections);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CollectionStoreRequest $request): CollectionResource
    {
        $data = $request->validated();
        $filters = $data['filters'];
        $collectionData = collect($data)->except('filters')->toArray();
        $collectionData['type'] = PlatformType::Snapchat;
        $collectionData['sales_type'] = SalesType::Paid;
        $collection = Collection::create($collectionData);

        $filtersData = collect($filters)->map(function ($filter) {
            $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $filter));
            $path = 'filters/' . uniqid() . '.png';
            Storage::put($path, $image, 'public');
            return $path;
        });

        $formattedFilters = collect($filtersData)->map(function ($filter) {
            return [
                'image' => $filter,
                'name' => strtoupper(Str::random(8))
            ];
        });

        $collection->filters()->createMany($formattedFilters);
        return new CollectionResource($collection->load('filters'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): CollectionResource
    {
        $collection = Collection::with(['user', 'filters', 'colors'])->where('is_active', true)->findOrFail($id);
        $favoriteCollections = auth('sanctum')->check() ? auth('sanctum')->user()->favoriteCollections()->pluck('collection_id') : collect();
        $purchasedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->purchases()->pluck('filter_id') : collect();
        $giftedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->gifts()->pluck('filter_id') : collect();
        $collection->is_favorite = $favoriteCollections->contains($collection->id);
        $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
            $filter->is_purchased = $purchasedFilters->contains($filter->id);
            $filter->is_gifted = $giftedFilters->contains($filter->id);
            return $filter;
        });
        return new CollectionResource($collection);
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
