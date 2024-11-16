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
            'id' => $this->id,
            'country' => CountryResource::make($this->whenLoaded('country')),
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'image' => $this->image,
            'type' => $this->type,
            'balance' => $this->balance,
            'coin' => $this->coin,
            'verified' => !!$this->email_verified_at,
            'is_active' => $this->is_active,
            'referral_code' => $this->referral_code,
            'received_coin' => $this->received_coin ?? false,
            'followers_count' => $this->followers_count,
            'followings_count' => $this->followings_count,
            'has_subscription' => $this->subscription && ($this->subscription->ends_at === null || $this->subscription->ends_at > now()),
        ];
    }
}
