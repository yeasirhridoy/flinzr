<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SpecialRequestResource extends JsonResource
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
            'category' => $this->category->name,
            'platform' => $this->platform,
            'occasion' => $this->occasion,
            'description' => $this->description,
            'image' => $this->image ? Storage::url($this->image) : null,
            'status' => $this->status,
        ];
    }
}
