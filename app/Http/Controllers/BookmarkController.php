<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Interest;
use App\Models\User;
use App\Services\MatchingEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    public function __construct(
        protected MatchingEngineService $matchingEngine
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user->isPro()) {
            return view('bookmarks.index', [
                'isPro' => false,
                'bookmarks' => collect(),
            ]);
        }

        $bookmarks = Bookmark::with(['bookmarkedUser.profile.primaryPhoto', 'bookmarkedUser.profile.photos'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $userProfile = $user->profile;

        $bookmarks->transform(function ($b) use ($userProfile, $user) {
            $candidate = $b->bookmarkedUser;
            $b->compatibility_score = ($userProfile && $candidate->profile)
                ? $this->matchingEngine->computeCompatibility($userProfile, $candidate->profile)
                : null;

            $b->existing_interest = Interest::where(function ($q) use ($user, $candidate) {
                $q->where('sender_id', $user->id)->where('recipient_id', $candidate->id);
            })->orWhere(function ($q) use ($user, $candidate) {
                $q->where('sender_id', $candidate->id)->where('recipient_id', $user->id);
            })->first();

            $b->is_photo_visible = $candidate->profile ? $candidate->profile->isPhotoVisibleTo($user) : false;

            return $b;
        });

        return view('bookmarks.index', [
            'isPro' => true,
            'bookmarks' => $bookmarks,
        ]);
    }

    public function toggle(Request $request, int $userId): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (! $user->isPro()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'upgrade_required' => true,
                    'message' => 'Shortlisting candidate profiles for family & Wali review is an exclusive Pro feature.',
                    'pricing_url' => route('subscription.pricing'),
                ], 403);
            }

            return redirect()->route('subscription.pricing')
                ->with('warning', 'Shortlisting candidate profiles for family & Wali review is an exclusive Pro feature. Upgrade now to save candidates.');
        }

        $targetUser = User::findOrFail($userId);

        if ($targetUser->id === $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You cannot shortlist yourself.'], 422);
            }

            return back()->with('error', 'You cannot shortlist yourself.');
        }

        $existing = Bookmark::where('user_id', $user->id)
            ->where('bookmarked_user_id', $targetUser->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $bookmarked = false;
            $msg = 'Candidate removed from your shortlisted profiles.';
        } else {
            Bookmark::create([
                'user_id' => $user->id,
                'bookmarked_user_id' => $targetUser->id,
                'notes' => $request->input('notes'),
            ]);
            $bookmarked = true;
            $msg = 'Candidate shortlisted! You can review them with your family anytime under Shortlisted Candidates.';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'bookmarked' => $bookmarked,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function updateNotes(Request $request, int $bookmarkId): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $bookmark = Bookmark::where('id', $bookmarkId)->where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $bookmark->update([
            'notes' => $request->input('notes'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Family consultation note updated successfully.',
            ]);
        }

        return back()->with('success', 'Family consultation note updated successfully.');
    }
}
