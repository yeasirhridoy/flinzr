<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MinimumUserResource extends JsonResource
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
            'username' => $this->username,
            'image' => $this->image,
            'followers_count' => $this->followers_count ?? $this->followers()->count(),
            'followings_count' => $this->followings_count ?? $this->followings()->count(),
            'is_following' => $this->when(auth('sanctum')->check(), $this->is_following ?? $this->followers->contains('id', auth('sanctum')->id())),
            'level' => $this->level ?? null,
            'download' => $this->download ?? null,
            'next_level_target' => $this->next_level_target ?? null,
            'percent_completed' => $this->percent_completed ?? null,
        ];
    }
}
