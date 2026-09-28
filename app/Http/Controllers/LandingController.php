<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_seekers' => User::where('role', 'seeker')->count(),
            'verified_profiles' => User::where('is_verified', true)->count(),
            'successful_matches' => 128, // Indicative showcase milestone
        ];

        $recentProfiles = Profile::with(['user', 'primaryPhoto'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true)->where('role', 'seeker'))
            ->latest()
            ->take(4)
            ->get();

        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('monthly_price')
            ->get();

        if ($plans->isEmpty()) {
            $plans = collect([
                new SubscriptionPlan(Subscription::getPlanDetails('free')),
                new SubscriptionPlan(Subscription::getPlanDetails('premium')),
                new SubscriptionPlan(Subscription::getPlanDetails('premium_plus')),
            ]);
        }

        return view('landing', compact('stats', 'recentProfiles', 'plans'));
    }
}
