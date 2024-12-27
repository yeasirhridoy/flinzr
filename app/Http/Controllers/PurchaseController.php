<?php

namespace App\Http\Controllers;

use App\Enums\CommissionLevel;
use App\Enums\Price;
use App\Http\Requests\CoinPurchaseRequest;
use App\Http\Requests\GiftFilterRequest;
use App\Http\Requests\PurchaseFilterRequest;
use App\Models\Favorite;
use App\Models\Filter;
use App\Models\Gift;
use App\Models\Purchase;
use App\Models\SpecialRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    public function purchaseCoin(CoinPurchaseRequest $request): JsonResponse
    {
        $data = $request->validated();

        $coin = auth()->user()->coin;

        switch ($data['product_id']) {
            case 'flinzr_175_coins':
                $coin += 175;
                break;
            case 'flinzr_375_coins':
                $coin += 375;
                break;
            case 'flinzr_475_coins':
                $coin += 475;
                break;
            case 'flinzr_675_coins':
                $coin += 675;
                break;
        }

        try {
            DB::beginTransaction();

            auth()->user()->update(['coin' => $coin]);
            $data['coins'] = $coin;
            auth()->user()->coinPurchases()->create($data);

            DB::commit();

            return response()->json(['message' => 'Purchase successful']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function purchaseFilter(PurchaseFilterRequest $request): JsonResponse
    {
        if (auth()->user()->filters->contains($request->filter_id)) {
            return response()->json(['message' => 'Filter already purchased'], 400);
        } else {
            DB::beginTransaction();
            $user = auth()->user();
            $coin = $user->coin;
            if ($coin < Price::Filter->getPrice()) {
                DB::rollBack();
                return response()->json(['message' => 'Insufficient coin'], 400);
            }
            $coin -= Price::Filter->getPrice();
            $user->update(['coin' => $coin]);
            $artist = Filter::findOrFail($request->filter_id)->collection->user;
            $commissionLevel = $artist->level;
            $percentage = $commissionLevel->getCommission();
            $earning = (Price::Filter->getPrice() / 25) * ($percentage / 100);
            Purchase::create([
                'user_id' => $user->id,
                'filter_id' => $request->filter_id,
                'artist_id' => $artist->id,
                'earning' => $earning,
                'amount' => Price::Filter->getPrice() / 25,
            ]);
            $collection = Filter::findOrFail($request->filter_id)->collection;
            $collection->updated_at = now();
            $collection->save();

            $artist->balance += $earning;
            $downloadCount = Purchase::where('artist_id', $artist->id)->count();
            if ($downloadCount > CommissionLevel::Level7->getTarget()) {
                $artist->level = CommissionLevel::Level8;
            } elseif ($downloadCount > CommissionLevel::Level6->getTarget()) {
                $artist->level = CommissionLevel::Level7;
            } elseif ($downloadCount > CommissionLevel::Level5->getTarget()) {
                $artist->level = CommissionLevel::Level6;
            } elseif ($downloadCount > CommissionLevel::Level4->getTarget()) {
                $artist->level = CommissionLevel::Level5;
            } elseif ($downloadCount > CommissionLevel::Level3->getTarget()) {
                $artist->level = CommissionLevel::Level4;
            } elseif ($downloadCount > CommissionLevel::Level2->getTarget()) {
                $artist->level = CommissionLevel::Level3;
            } elseif ($downloadCount > CommissionLevel::Level1->getTarget()) {
                $artist->level = CommissionLevel::Level2;
            } else {
                $artist->level = CommissionLevel::Level1;
            }
            $artist->save();
            auth()->user()->filters()->syncWithoutDetaching($request->filter_id);

            if ($user->purchases()->count() == 1) {
                $referredBy = $user->referred_by;
                if ($referredBy) {
                    $referrer = User::where('referral_code', $referredBy)->first();
                    if ($referrer) {
                        $referrer->coin = $referrer->coin + 25;
                        $referrer->save();
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Purchase successful']);
        }
    }

    public function giftFilter(GiftFilterRequest $request): JsonResponse
    {
        $user = User::query()->where('username',$request->username)->first();
        if ($user->filters->pluck('id')->contains($request->filter_id)) {
            return response()->json(['message' => 'Filter already purchased'], 400);
        } else {
            DB::beginTransaction();
            $sender = auth()->user();
            $coin = $sender->coin;
            if ($coin < Price::GiftFilter->getPrice()) {
                DB::rollBack();
                return response()->json(['message' => 'Insufficient coin'], 400);
            }
            $coin -= Price::GiftFilter->getPrice();
            $sender->update(['coin' => $coin]);
            $artist = Filter::findOrFail($request->filter_id)->collection->user;
            $commissionLevel = $artist->level;
            $percentage = $commissionLevel->getCommission();
            $earning = (Price::GiftFilter->getPrice() / 25) * ($percentage / 100);
            Gift::create([
                'user_id' => $user->id,
                'sender_id' => $sender->id,
                'filter_id' => $request->filter_id,
                'artist_id' => $artist->id,
                'earning' => $earning,
                'amount' => Price::GiftFilter->getPrice() / 25,
            ]);
            $user->filters()->syncWithoutDetaching($request->filter_id);
            DB::commit();
            return response()->json(['message' => 'Gift successful']);
        }
    }

    public function profileCounter(): JsonResponse
    {
        $purchaseCount = Purchase::where('user_id', auth()->id())->count();
        $giftCount = Gift::where('sender_id', auth()->id())->count();
        $favourites = Favorite::where('user_id', auth()->id())->count();
        $specialRequestCount = SpecialRequest::where('user_id', auth()->id())->whereNotNull('url')->count();


        return response()->json([
            'purchase_count' => $purchaseCount,
            'gift_count' => $giftCount,
            'favourites' => $favourites,
            'special_filters' => $specialRequestCount
        ]);
    }
}
