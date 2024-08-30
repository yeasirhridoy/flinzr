<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'eng_name' => $this->eng_name,
            'arabic_name' => $this->arabic_name,
            'image' => $this->image ? Storage::url($this->image) : null,
        ];
    }
}