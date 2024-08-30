<?php

namespace App\Http\Resources;

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
            'country_id' => $this->country_id,
            'name' => $this->name,
            'email' => $this->email,
            'image' => $this->image ? Storage::url($this->image) : null,
            'type' => $this->type,
            'balance' => $this->balance,
            'coin' => $this->coin,
            'verified' => !!$this->email_verified_at,
            'is_active' => $this->is_active,
        ];
    }
}