<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Mail\OtpMail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->image ? Storage::url($this->image) : "https://ui-avatars.com/api/?name=" . urlencode($this->name);
    }

    protected static function booted()
    {
        parent::saving(function (User $user) {
            if ($user->isDirty('type') && $user->getOriginal('type') === UserType::Influencer && $user->type !== UserType::Influencer) {
                $user->coin = 0;
            }
            if ($user->isDirty('is_active') && $user->type === UserType::Influencer && $user->is_active === false) {
                $user->coin = 0;
            }
        });
        parent::saved(function (User $user) {
            if ($user->isDirty('is_active') && $user->type === UserType::Artist && $user->is_active === false) {
                $user->collections()->update(['is_active' => false]);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type' => UserType::class,
            'status' => UserStatus::class,
            'level' => \App\Enums\CommissionLevel::class
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function balance(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100, set: fn($value) => $value * 100
        );
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class)->withTimestamps();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteCollections(): HasManyThrough
    {
        return $this->hasManyThrough(Collection::class, Favorite::class, 'user_id', 'id', 'id', 'collection_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function followings(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Follow::class, 'follower_id', 'id', 'id', 'followee_id');
    }

    public function followers(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Follow::class, 'followee_id', 'id', 'id', 'follower_id');
    }

    public function artistRequest(): HasOne
    {
        return $this->hasOne(ArtistRequest::class);
    }

    public function influencerRequest(): HasOne
    {
        return $this->hasOne(InfluencerRequest::class);
    }

    public function payoutRequest(): HasOne
    {
        return $this->hasOne(PayoutRequest::class);
    }

    public function coinPurchases(): HasMany
    {
        return $this->hasMany(CoinPurchase::class);
    }

    public function sendOtp(): void
    {
        if (!cache()->has('otp_' . $this->email)) {
            $otp = mt_rand(100000, 999999);
            cache()->remember('otp_' . $this->email, now()->addMinutes(5), fn() => $otp);
            Mail::to($this->email)->send(new OtpMail($otp));
        }
    }

    public function validateOtp($otp): bool
    {
        return cache()->get('otp_' . $this->email) == $otp;
    }
}
