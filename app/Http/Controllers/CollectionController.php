<?php

namespace App\Http\Controllers;

use App\Enums\PlatformType;
use App\Enums\RequestStatus;
use App\Enums\SalesType;
use App\Http\Requests\CollectionStoreRequest;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\FilterResource;
use App\Models\Collection;
use App\Models\Country;
use App\Models\Filter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $purchasedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->purchases()->get(['filter_id', 'created_at'])
            ->mapWithKeys(fn($purchase) => [$purchase->filter_id => $purchase->created_at]) : collect();
        $giftedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->gifts()->get(['filter_id', 'created_at'])
            ->mapWithKeys(fn($gift) => [$gift->filter_id => $gift->created_at]) : collect();

        $purchasedCollections->map(function ($collection) use ($giftedFilters, $purchasedFilters, $favoriteCollections) {
            $collection->is_favorite = $favoriteCollections->contains($collection->id);
            $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
                $filter->is_purchased = $purchasedFilters->has($filter->id);
                $filter->is_gifted = $giftedFilters->has($filter->id);

                $filter->purchased_at = $purchasedFilters->get($filter->id, null);
                $filter->gifted_at = $giftedFilters->get($filter->id, null);
                return $filter;
            });
            return $collection;
        });

        return CollectionResource::collection($purchasedCollections);
    }

    public function giftedCollections(): AnonymousResourceCollection
    {
        $giftedCollections = Collection::with(['user', 'filters' => function ($q2) {
            $q2->whereIn('id', auth('sanctum')->user()->gifts()->pluck('filter_id'));

        },
            'colors'])->whereHas('filters', function ($query) {
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

    public function giftedFilters()
    {

        $filterIds = auth('sanctum')->user()->gifts()->pluck('filter_id');
        $purchasedFilters = auth('sanctum')->check() ? auth('sanctum')->user()->purchases()->pluck('filter_id') : collect();

        $filters = Filter::query()->with('collection')->find($filterIds);

        $filters->map(function ($filter) use ($purchasedFilters) {
            $filter->is_purchased = $purchasedFilters->contains($filter->id);
            $filter->is_gifted = true;
            return $filter;
        });

        return FilterResource::collection($filters);
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

        $trendingCollections = $collectionsQuery->clone()->where('is_trending', true)->get()->map(function ($collection) use ($giftedFilters, $purchasedFilters, $favoriteCollections) {
            $collection->is_favorite = $favoriteCollections->contains($collection->id);
            $collection->filters->map(function ($filter) use ($giftedFilters, $purchasedFilters) {
                $filter->is_purchased = $purchasedFilters->contains($filter->id);
                $filter->is_gifted = $giftedFilters->contains($filter->id);
                return $filter;
            });
            return $collection;
        });
        $featuredCollections = $collectionsQuery->clone()->where('is_featured', true)->get()->map(function ($collection) use ($giftedFilters, $purchasedFilters, $favoriteCollections) {
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
            'banner' => 'in:true',
            'with_banner' => 'in:true',
            'query' => 'string',
            'tags' => 'string|regex:/^[0-9,]+$/',
            'colors' => 'string|regex:/^[0-9,]+$/',
            'sales_type' => [Rule::in(SalesType::values())],
        ];

        request()->validate($rules);

        $collections = Collection::query()
            ->where('is_active', true)
            ->with(['user', 'filters', 'colors'])
            ->has('filters')
            ->orderBy('order_column');


        if (request()->filled('type')) {
            $collections->where('type', request('type'));
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

        if (request()->filled('trending') && request('trending') === 'true') {
            $collections->where('is_trending', true);
        }

        if (request()->filled('banner') && request('banner') === 'true') {
            $collections->where('is_banner', true);
        } elseif (request()->filled('with_banner') && request('with_banner') === 'true') {
            $collections->where('is_banner', true)->orWhere('is_banner', false);
        } else {
            $collections->where('is_banner', false);
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

        if (request()->filled('country_code')) {
            $countryId = Country::where('code', request('country_code'))->first()->id;
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

        if (request('banner') === 'true') {
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
     * @throws ValidationException
     */

    public function store(CollectionStoreRequest $request): CollectionResource
    {
        $data = $request->validated();
        $user = $request->user();
        $filters = $data['filters'];

        $pendingRequests = Collection::where('user_id', $user->id)
            ->where('is_active', false)
            ->exists();
        $activeCollections = Collection::where('user_id', $user->id)
            ->where('is_active', true)
            ->count();

        if ($pendingRequests && $activeCollections === 0) {
            throw ValidationException::withMessages([
                'request' => 'You have a pending request. Please wait until it is processed.'
            ]);
        }

        $currentMonthCount = Collection::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->count();

        if ($currentMonthCount >= 6) {
            throw ValidationException::withMessages([
                'request' => 'You can only submit up to 6 collections per month.'
            ]);
        }

        $collectionData = collect($data)->except('filters')->toArray();
        $collectionData['type'] = PlatformType::Snapchat;
        $collectionData['sales_type'] = SalesType::Paid;
        $collectionData['user_id'] = $user->id; // Ensure the collection is associated with the user

        if ($request->filled('cover')) {
            $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $request->cover));
            $path = 'collections/' . uniqid() . '.png';
            Storage::put($path, $image, 'public');
            $collectionData['cover'] = $path;
            $collectionData['avatar'] = $path;
            $collectionData['thumbnail'] = $path;
        }

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

        return new CollectionResource($collection->refresh()->load('filters'));
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
