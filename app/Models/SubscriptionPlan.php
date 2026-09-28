<?php

namespace App\Models;

use Database\Factories\SubscriptionPlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    /** @use HasFactory<SubscriptionPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'annual_price',
        'daily_profile_views',
        'daily_interests',
        'advanced_filters',
        'profile_boost',
        'see_who_viewed',
        'dedicated_advisor',
        'badge_text',
        'features',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'annual_price' => 'decimal:2',
            'daily_profile_views' => 'integer',
            'daily_interests' => 'integer',
            'advanced_filters' => 'boolean',
            'profile_boost' => 'boolean',
            'see_who_viewed' => 'boolean',
            'dedicated_advisor' => 'boolean',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer',
            'features' => 'array',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan', 'slug');
    }

    public function isFree(): bool
    {
        return (float) $this->monthly_price <= 0.0;
    }

    public function isUnlimitedViews(): bool
    {
        return $this->daily_profile_views < 0 || $this->daily_profile_views >= 100000;
    }

    public function isUnlimitedInterests(): bool
    {
        return $this->daily_interests < 0 || $this->daily_interests >= 100000;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('monthly_price');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('monthly_price', '>', 0);
    }

    /**
     * Convert to array compatible with legacy getPlanDetails() format.
     */
    public function toLegacyPlanDetails(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'daily_profile_views' => $this->isUnlimitedViews() ? PHP_INT_MAX : $this->daily_profile_views,
            'daily_interests' => $this->isUnlimitedInterests() ? PHP_INT_MAX : $this->daily_interests,
            'advanced_filters' => (bool) $this->advanced_filters,
            'profile_boost' => (bool) $this->profile_boost,
            'see_who_viewed' => (bool) $this->see_who_viewed,
            'dedicated_advisor' => (bool) $this->dedicated_advisor,
            'badge_text' => $this->badge_text,
            'is_popular' => (bool) $this->is_popular,
            'features' => $this->features ?? [],
            'monthly_price' => (float) $this->monthly_price,
            'annual_price' => (float) $this->annual_price,
        ];
    }
}
