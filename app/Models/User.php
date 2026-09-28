<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_verified_at',
        'email_verified_at',
        'password',
        'role',
        'permissions',
        'gender',
        'dob',
        'marital_status',
        'is_verified',
        'is_active',
        'two_factor_enabled',
        'two_factor_secret',
        'deactivation_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'dob' => 'date',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'permissions' => 'array',
            'password' => 'hashed',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function photos(): HasMany
    {
        return $this->hasManyThrough(Photo::class, Profile::class);
    }

    public function sentInterests(): HasMany
    {
        return $this->hasMany(Interest::class, 'sender_id');
    }

    public function receivedInterests(): HasMany
    {
        return $this->hasMany(Interest::class, 'recipient_id');
    }

    public function waliLinksAsSeeker(): HasMany
    {
        return $this->hasMany(WaliLink::class, 'seeker_user_id');
    }

    public function waliLinksAsWali(): HasMany
    {
        return $this->hasMany(WaliLink::class, 'wali_user_id');
    }

    /**
     * Get active linked Wali user if seeker is Wali-dependent.
     */
    public function getActiveWaliUser(): ?self
    {
        $link = $this->waliLinksAsSeeker()
            ->where('status', 'active')
            ->whereNotNull('wali_user_id')
            ->with('wali')
            ->first();

        return $link?->wali;
    }

    /**
     * Check if user is Wali dependent (active link or wali_required flag on profile).
     */
    public function isWaliDependent(): bool
    {
        return $this->waliLinksAsSeeker()->where('status', 'active')->exists()
            || (bool) ($this->profile?->wali_required ?? false);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('role', 'last_read_at', 'is_typing')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->latestOfMany();
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_id');
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function profileViewsGiven(): HasMany
    {
        return $this->hasMany(ProfileView::class, 'viewer_id');
    }

    public function profileViewsReceived(): HasMany
    {
        return $this->hasMany(ProfileView::class, 'viewed_id');
    }

    // Role helpers
    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin'], true);
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['moderator', 'admin', 'super_admin'], true);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions, true);
    }

    public function isWali(): bool
    {
        return $this->role === 'wali';
    }

    public function isSeeker(): bool
    {
        return $this->role === 'seeker';
    }

    public function getAgeAttribute(): ?int
    {
        return $this->dob ? Carbon::parse($this->dob)->age : null;
    }

    public function getPlanAttribute(): string
    {
        $sub = $this->activeSubscription;

        return $sub ? $sub->plan : 'free';
    }

    public function isPremium(): bool
    {
        if ($this->plan === 'free') {
            return false;
        }

        $sub = $this->activeSubscription;
        if (! $sub || ! $sub->isActive()) {
            return false;
        }

        return true;
    }

    public function isPremiumPlus(): bool
    {
        if ($this->plan === 'premium_plus') {
            return true;
        }

        $planDetails = Subscription::getPlanDetails($this->plan);

        return ! empty($planDetails['profile_boost']);
    }

    /**
     * Send password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
