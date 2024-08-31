<?php

namespace App\Http\Resources;

use App\Enums\UserType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'country' => CountryResource::make($this->whenLoaded('country')),
            'username' => $this->name,
            'email' => $this->email,
            'image' => $this->image ? Storage::url($this->image) : null,
            'type' => $this->type,
            'balance' => $this->balance,
            'coin' => $this->coin,
            'verified' => !!$this->email_verified_at,
            'is_active' => $this->is_active,
            'followers_count' => $this->followers_count,
            'followings_count' => $this->followings_count,
        ];
    }
}
