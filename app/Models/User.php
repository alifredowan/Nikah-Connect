<?php

namespace App\Models;

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
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['moderator', 'admin'], true);
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
        return in_array($this->plan, ['premium', 'premium_plus'], true);
    }

    public function isPremiumPlus(): bool
    {
        return $this->plan === 'premium_plus';
    }
}
