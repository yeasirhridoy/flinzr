<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use App\Enums\UserType;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, SoftDeletes;

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->image ? Storage::url($this->image) : "https://ui-avatars.com/api/?name=".urlencode($this->name);
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
            'status' => UserStatus::class
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }
}
