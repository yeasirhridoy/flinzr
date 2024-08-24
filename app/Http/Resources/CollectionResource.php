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
            'type' => $this->type,
            'eng_name' => $this->eng_name,
            'eng_description' => $this->eng_description,
            'arabic_name' => $this->arabic_name,
            'arabic_description' => $this->arabic_description,
            'sales_type' => $this->sales_type,
            'avatar' => $this->avatar ? Storage::url($this->avatar) : null,
            'thumbnail' => $this->avatar ? Storage::url($this->thumbnail) : null,
            'cover' => $this->avatar ? Storage::url($this->cover) : null,
            'is_active' => $this->is_active,
            'user'=> new MinimumUserResource($this->whenLoaded('user')),
            'filters' => FilterResource::collection($this->whenLoaded('filters')),
        ];
    }
}
