<?php

namespace App\Http\Resources;

use App\Enums\UserType;
use App\Models\Favorite;
use App\Models\Gift;
use App\Models\Purchase;
use App\Models\SpecialRequest;
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
        $purchaseCount = Purchase::where('user_id', $this->id)->count();
        $giftCount = Gift::where('sender_id', $this->id)->count();
        $favourites = Favorite::where('user_id', $this->id)->count();
        $specialRequestCount = SpecialRequest::where('user_id', $this->id)->whereNotNull('url')->count();

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
            'counter' => [
                'purchase_count' => $purchaseCount,
                'gift_count' => $giftCount,
                'favourites' => $favourites,
                'special_filters' => $specialRequestCount
            ]
        ];
    }
}
