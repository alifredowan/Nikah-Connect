<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use App\Models\ProfileView;
use App\Services\MatchingEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileVisitorController extends Controller
{
    public function __construct(
        protected MatchingEngineService $matchingEngine
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $isPro = $user->isPro();

        // Get unique visitors who are opposite gender and active seekers
        $targetGender = $user->gender === 'male' ? 'female' : ($user->gender === 'female' ? 'male' : null);

        $query = ProfileView::with(['viewer.profile.primaryPhoto', 'viewer.profile.photos'])
            ->where('viewed_id', $user->id)
            ->whereHas('viewer', function ($q) use ($targetGender) {
                $q->where('is_active', true)
                    ->where('role', 'seeker');
                if ($targetGender) {
                    $q->where('gender', $targetGender);
                }
            })
            ->latest('created_at');

        $totalViewsCount = (clone $query)->count();
        $uniqueVisitorsCount = (clone $query)->distinct('viewer_id')->count('viewer_id');

        $visitors = collect();
        if ($isPro) {
            // Group by viewer_id to show unique recent visitors with their latest visit time
            $rawVisitors = $query->get()->unique('viewer_id')->values();

            $userProfile = $user->profile;
            $visitors = $rawVisitors->map(function ($view) use ($userProfile, $user) {
                $viewer = $view->viewer;
                $compatibility = null;
                if ($userProfile && $viewer->profile) {
                    $compatibility = $this->matchingEngine->computeCompatibility($userProfile, $viewer->profile);
                }

                $existingInterest = Interest::where(function ($q) use ($user, $viewer) {
                    $q->where('sender_id', $user->id)->where('recipient_id', $viewer->id);
                })->orWhere(function ($q) use ($user, $viewer) {
                    $q->where('sender_id', $viewer->id)->where('recipient_id', $user->id);
                })->first();

                $view->viewer_compatibility = $compatibility;
                $view->existing_interest = $existingInterest;
                $view->is_photo_visible = $viewer->profile ? $viewer->profile->isPhotoVisibleTo($user) : false;

                return $view;
            });
        }

        return view('profile.visitors', compact('isPro', 'visitors', 'totalViewsCount', 'uniqueVisitorsCount', 'user'));
    }

    public function toggleIncognito(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->isPro()) {
            return redirect()->route('subscription.pricing')
                ->with('warning', 'Incognito (Discreet Browsing) is an exclusive Pro feature. Upgrade your plan to browse candidate profiles privately.');
        }

        $user->is_incognito = ! $user->is_incognito;
        $user->save();

        $statusMsg = $user->is_incognito
            ? 'Incognito Mode is now ON. You can now explore candidate profiles discreetly without appearing in their visitor logs.'
            : 'Incognito Mode is now OFF. Other candidates can see when you view their profile.';

        return back()->with('success', $statusMsg);
    }
}
