<?php

namespace App\Services;

use App\Models\Interest;
use App\Models\ProfileView;
use App\Models\Subscription;
use App\Models\User;

class QuotaService
{
    /**
     * Check if user can view more profiles today (FR-3.4).
     */
    public function canViewProfile(User $viewer, int $viewedUserId): bool
    {
        if ($viewer->id === $viewedUserId || $viewer->isAdmin() || $viewer->isModerator()) {
            return true;
        }

        if ($viewer->isPremium()) {
            return true;
        }

        $limits = Subscription::getPlanDetails($viewer->plan);
        $maxDailyViews = $limits['daily_profile_views'];

        $viewsToday = ProfileView::where('viewer_id', $viewer->id)
            ->where('viewed_date', now()->toDateString())
            ->distinct('viewed_id')
            ->count('viewed_id');

        return $viewsToday < $maxDailyViews;
    }

    /**
     * Record a profile view if not already recorded today.
     */
    public function recordView(User $viewer, int $viewedUserId): void
    {
        if ($viewer->id === $viewedUserId) {
            return;
        }

        ProfileView::firstOrCreate([
            'viewer_id' => $viewer->id,
            'viewed_id' => $viewedUserId,
            'viewed_date' => now()->toDateString(),
        ]);
    }

    /**
     * Check if user can send an interest request today (FR-5.1).
     */
    public function canSendInterest(User $user): bool
    {
        if ($user->isPremium()) {
            return true;
        }

        $limits = Subscription::getPlanDetails($user->plan);
        $maxInterests = $limits['daily_interests'];

        $interestsToday = Interest::where('sender_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return $interestsToday < $maxInterests;
    }

    /**
     * Get remaining quotas for display on dashboard/profile.
     */
    public function getUsageStats(User $user): array
    {
        $limits = Subscription::getPlanDetails($user->plan);

        if ($user->isPremium()) {
            return [
                'plan' => $user->plan,
                'views_used' => ProfileView::where('viewer_id', $user->id)->where('viewed_date', now()->toDateString())->count(),
                'views_limit' => 'Unlimited',
                'interests_used' => Interest::where('sender_id', $user->id)->whereDate('created_at', now()->toDateString())->count(),
                'interests_limit' => 'Unlimited',
                'can_use_advanced_filters' => true,
            ];
        }

        $viewsUsed = ProfileView::where('viewer_id', $user->id)
            ->where('viewed_date', now()->toDateString())
            ->count();

        $interestsUsed = Interest::where('sender_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return [
            'plan' => 'free',
            'views_used' => $viewsUsed,
            'views_limit' => $limits['daily_profile_views'],
            'views_remaining' => max(0, $limits['daily_profile_views'] - $viewsUsed),
            'interests_used' => $interestsUsed,
            'interests_limit' => $limits['daily_interests'],
            'interests_remaining' => max(0, $limits['daily_interests'] - $interestsUsed),
            'can_use_advanced_filters' => false,
        ];
    }
}
