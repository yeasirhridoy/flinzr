<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistRequestResource extends JsonResource
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
            'country_id' => $this->country_id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'id_no' => $this->id_no,
            'url' => $this->url,
            'status' => $this->status,
        ];
    }
}
