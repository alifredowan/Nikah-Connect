<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_percentage',
        'plan_slug',
        'allowed_emails',
        'description',
        'max_uses',
        'times_used',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'integer',
            'max_uses' => 'integer',
            'times_used' => 'integer',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_slug', 'slug');
    }

    public function isRestrictedToUsers(): bool
    {
        return ! empty(trim((string) $this->allowed_emails));
    }

    /**
     * @return array<int, string>
     */
    public function getAllowedEmailsList(): array
    {
        if (! $this->isRestrictedToUsers()) {
            return [];
        }

        $raw = (string) $this->allowed_emails;

        if (str_starts_with($raw, '[') && str_ends_with($raw, ']')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_map('strtolower', array_map('trim', $decoded));
            }
        }

        // Split by comma, newline, or semicolon
        $emails = preg_split('/[\r\n,;]+/', $raw);

        return array_values(array_filter(array_map('strtolower', array_map('trim', (array) $emails))));
    }

    public function isUserEligible(?User $user): bool
    {
        if (! $this->isRestrictedToUsers()) {
            return true;
        }

        if (! $user || empty($user->email)) {
            return false;
        }

        return in_array(strtolower(trim($user->email)), $this->getAllowedEmailsList(), true);
    }

    public function isApplicableToPlan(?string $planSlug): bool
    {
        if (empty($this->plan_slug) || $this->plan_slug === 'all') {
            return true;
        }

        if (empty($planSlug)) {
            return true;
        }

        return strtolower($this->plan_slug) === strtolower($planSlug);
    }

    /**
     * Check comprehensive validity including target plan and special user restrictions.
     *
     * @return array{valid: bool, reason: ?string}
     */
    public function checkValidity(?User $user = null, ?string $planSlug = null): array
    {
        if (! $this->is_active) {
            return ['valid' => false, 'reason' => 'This promo code is currently inactive.'];
        }

        if ($this->times_used >= $this->max_uses) {
            return ['valid' => false, 'reason' => 'This promo code has reached its maximum redemptions limit.'];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return ['valid' => false, 'reason' => 'This promo code has expired.'];
        }

        if ($planSlug && ! $this->isApplicableToPlan($planSlug)) {
            $planName = $this->plan?->name ?? strtoupper($this->plan_slug);

            return ['valid' => false, 'reason' => "This promo code is exclusively reserved for the {$planName} package."];
        }

        if ($this->isRestrictedToUsers() && ! $this->isUserEligible($user)) {
            return ['valid' => false, 'reason' => 'This special discount code is restricted to designated VIP/special accounts.'];
        }

        return ['valid' => true, 'reason' => null];
    }

    public function isValid(?User $user = null, ?string $planSlug = null): bool
    {
        return $this->checkValidity($user, $planSlug)['valid'];
    }
}
