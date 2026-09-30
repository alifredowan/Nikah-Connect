<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use App\Models\Profile;
use App\Models\SavedSearch;
use App\Models\User;
use App\Services\MatchingEngineService;
use App\Services\QuotaService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DiscoveryController extends Controller
{
    public function __construct(
        protected MatchingEngineService $matchingEngine,
        protected QuotaService $quotaService
    ) {}

    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        $userProfile = $currentUser->profile;

        // Determine strict target opposite gender
        // Male logged in -> ONLY female seeker profiles
        // Female logged in -> ONLY male seeker profiles
        if ($currentUser->gender === 'male') {
            $targetGender = 'female';
        } elseif ($currentUser->gender === 'female') {
            $targetGender = 'male';
        } else {
            $targetGender = $request->input('gender', 'female');
        }

        $query = Profile::with(['user', 'primaryPhoto', 'photos'])
            ->where('user_id', '!=', $currentUser->id)
            ->whereHas('user', function ($q) use ($targetGender) {
                $q->where('is_active', true)
                    ->where('role', 'seeker')
                    ->where('gender', $targetGender);
            });

        // Advanced filter gating check:
        $isSearching = $request->filled('sect') || $request->filled('prayer_frequency');
        if ($isSearching && ! $currentUser->isPremium() && ! $currentUser->isAdmin()) {
            // Free members can still filter by basic fields: age, country, marital_status
        }

        // Basic Filters
        if ($request->filled('country')) {
            $query->where('country', 'like', '%'.$request->country.'%');
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }
        if ($request->filled('marital_status')) {
            $query->whereHas('user', fn ($q) => $q->where('marital_status', $request->marital_status));
        }

        // Age filter
        if ($request->filled('age_min')) {
            $maxDob = Carbon::now()->subYears((int) $request->age_min)->toDateString();
            $query->whereHas('user', fn ($q) => $q->where('dob', '<=', $maxDob));
        }
        if ($request->filled('age_max')) {
            $minDob = Carbon::now()->subYears((int) $request->age_max + 1)->toDateString();
            $query->whereHas('user', fn ($q) => $q->where('dob', '>=', $minDob));
        }

        // Advanced Filters (religiosity & education)
        if ($request->filled('sect')) {
            $query->where('sect_madhhab', $request->sect);
        }
        if ($request->filled('prayer_frequency')) {
            $query->where('prayer_frequency', $request->prayer_frequency);
        }
        if ($request->filled('education_level')) {
            $query->where('education_level', $request->education_level);
        }
        if ($request->boolean('wali_required')) {
            $query->where('wali_required', true);
        }

        $profiles = $query->paginate(12)->withQueryString();

        // Calculate compatibility score for each result
        if ($userProfile) {
            $profiles->getCollection()->transform(function ($profile) use ($userProfile) {
                $profile->compatibility_score = $this->matchingEngine->computeCompatibility($userProfile, $profile);

                return $profile;
            });
        }

        // Top algorithmic daily recommendations
        $dailyRecommendations = $this->matchingEngine->getDailyMatches($currentUser, 6);

        // Daily quotas for current user
        $quotaStats = $this->quotaService->getUsageStats($currentUser);

        return view('discovery.index', compact('profiles', 'dailyRecommendations', 'quotaStats'));
    }

    public function show(int $userId): View|RedirectResponse
    {
        $currentUser = Auth::user();

        $targetUser = User::with(['profile.photos', 'profile.primaryPhoto'])->findOrFail($userId);
        $profile = $targetUser->profile;

        if (! $profile || ! $targetUser->is_active || $targetUser->role !== 'seeker' || $targetUser->id === $currentUser->id) {
            return redirect()->route('discovery.index')->with('error', 'Profile not found or not available for matrimonial matching.');
        }

        if ($currentUser->role === 'seeker' && $currentUser->gender && $targetUser->gender && $currentUser->gender === $targetUser->gender) {
            return redirect()->route('discovery.index')->with('error', 'You can only view candidate profiles of the opposite gender.');
        }

        // Enforce daily profile view limit (FR-3.4)
        if (! $this->quotaService->canViewProfile($currentUser, $targetUser->id)) {
            return redirect()->route('subscription.pricing')->with('warning', 'You have reached your free limit of 10 profile views per day. Upgrade to Premium for unlimited views!');
        }

        $this->quotaService->recordView($currentUser, $targetUser->id);

        $compatibilityScore = null;
        if ($currentUser->profile) {
            $compatibilityScore = $this->matchingEngine->computeCompatibility($currentUser->profile, $profile);
        }

        // Check photo visibility permission (blurred vs visible)
        $isPhotoVisible = $profile->isPhotoVisibleTo($currentUser);

        // Check existing interest relationship
        $existingInterest = Interest::where(function ($q) use ($currentUser, $targetUser) {
            $q->where('sender_id', $currentUser->id)->where('recipient_id', $targetUser->id);
        })->orWhere(function ($q) use ($currentUser, $targetUser) {
            $q->where('sender_id', $targetUser->id)->where('recipient_id', $currentUser->id);
        })->first();

        // Check active linked Wali
        $activeWaliLink = $targetUser->waliLinksAsSeeker()->where('status', 'active')->first();

        return view('discovery.show', compact('targetUser', 'profile', 'compatibilityScore', 'isPhotoVisible', 'existingInterest', 'activeWaliLink'));
    }

    public function saveSearch(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:100'],
        ]);

        $filters = $request->except(['_token', 'title']);

        SavedSearch::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'filters' => $filters,
            'alerts_enabled' => true,
        ]);

        return back()->with('success', 'Search criteria saved! You will receive alerts when matching candidates join (FR-3.3).');
    }
}
