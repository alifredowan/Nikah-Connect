<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan',
        'billing_cycle',
        'status',
        'starts_at',
        'ends_at',
        'renews_at',
        'payment_method',
        'amount_paid',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'renews_at' => 'datetime',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && (! $this->ends_at || $this->ends_at->isFuture());
    }

    public static function getPlanDetails(string $plan): array
    {
        return match ($plan) {
            'premium_plus' => [
                'name' => 'Premium+',
                'daily_profile_views' => PHP_INT_MAX,
                'daily_interests' => PHP_INT_MAX,
                'advanced_filters' => true,
                'profile_boost' => true,
                'see_who_viewed' => true,
                'dedicated_advisor' => true,
                'monthly_price' => 39.99,
                'annual_price' => 299.99,
            ],
            'premium' => [
                'name' => 'Premium',
                'daily_profile_views' => PHP_INT_MAX,
                'daily_interests' => PHP_INT_MAX,
                'advanced_filters' => true,
                'profile_boost' => false,
                'see_who_viewed' => true,
                'dedicated_advisor' => false,
                'monthly_price' => 19.99,
                'annual_price' => 159.99,
            ],
            default => [
                'name' => 'Free',
                'daily_profile_views' => 10,
                'daily_interests' => 5,
                'advanced_filters' => false,
                'profile_boost' => false,
                'see_who_viewed' => false,
                'dedicated_advisor' => false,
                'monthly_price' => 0.00,
                'annual_price' => 0.00,
            ],
        };
    }
}
