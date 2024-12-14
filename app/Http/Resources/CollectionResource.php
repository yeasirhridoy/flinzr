<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'type' => $this->type,
            'eng_name' => $this->eng_name,
            'eng_description' => $this->eng_description,
            'arabic_name' => $this->arabic_name,
            'arabic_description' => $this->arabic_description,
            'sales_type' => $this->sales_type,
            'is_active' => $this->is_active,
            'is_banner' => $this->is_banner,
            'is_favorite' => $this->is_favorite,
            'add_favorite_at' => $this->add_favorite_at,
            'is_featured' => $this->is_featured,
            'is_trending' => $this->is_trending,
            'status' => $this->status,
            'avatar' => $this->avatar ? Storage::url($this->avatar) : null,
            'thumbnail' => $this->avatar ? Storage::url($this->thumbnail) : null,
            'cover' => $this->avatar ? Storage::url($this->cover) : null,
            'user'=> new MinimumUserResource($this->whenLoaded('user')),
            'filters' => FilterResource::collection($this->whenLoaded('filters')->sortByDesc('is_purchased')),
            'colors' => ColorResource::collection($this->whenLoaded('colors')),
            'created_at' => $this->created_at,
            'last_purchased_at' => $this->updated_at,
        ];
    }
}
