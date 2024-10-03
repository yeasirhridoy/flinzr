<?php

namespace App\Http\Resources;

use App\Models\Gift;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FilterResource extends JsonResource
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
            'name' => $this->name,
            'image' => $this->image ? Storage::url($this->image) : null,
            'url' => $this->url,
            'is_purchased' => $this->is_purchased,
            'is_gifted' => $this->is_gifted,
            'username' => $this->when($this->is_gifted, Gift::query()->where('user_id', auth('sanctum')->id())->first()->sender->username),
        ];
    }
}
