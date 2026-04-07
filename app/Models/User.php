<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasApiTokens, HasFactory, Notifiable;

    const TIER_BASIC_GLOW = 'basic_glow';
    const TIER_SILVER_SHINE = 'silver_shine';
    const TIER_GOLDEN_VIBE = 'golden_vibe';
    const TIER_DIAMOND_SLAY = 'diamond_slay';

    public static function getTiers(): array
    {
        return [
            self::TIER_BASIC_GLOW => [
                'name_ar' => 'Basic Glow',
                'threshold' => 0,
                'rate' => 0.05,
            ],
            self::TIER_SILVER_SHINE => [
                'name_ar' => 'Silver Shine',
                'threshold' => 1000,
                'rate' => 0.07,
            ],
            self::TIER_GOLDEN_VIBE => [
                'name_ar' => 'Golden Vibe',
                'threshold' => 5000,
                'rate' => 0.10,
            ],
            self::TIER_DIAMOND_SLAY => [
                'name_ar' => 'Diamond Slay',
                'threshold' => 15000,
                'rate' => 0.15,
            ],
        ];
    }

    public function salons(): HasMany
    {
        return $this->hasMany(Salon::class, 'user_id');
    }

    public function getTenants(Panel $panel): array|Collection
    {
        // Admin panel doesn't use tenancy
        if ($panel->getId() === 'admin') {
            return collect([]);
        }
        return $this->salons;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->salons()->whereKey($tenant)->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return (bool) $this->is_admin;
        }
        // Salon owners can access salon panel
        return true;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_admin',
        'loyalty_points',
        'google_id',
        'facebook_id',
        'avatar',
        'referral_code',
        'referred_by_id',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(Str::random(8));
            }
        });
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function lastBooking()
    {
        return $this->hasOne(Booking::class)->latestOfMany();
    }

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
        ];
    }
}
