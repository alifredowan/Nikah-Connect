<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use App\Services\MatchingEngineService;
use App\Services\QuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoveryApiController extends Controller
{
    public function __construct(
        protected MatchingEngineService $matchingEngine,
        protected QuotaService $quotaService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $oppositeGender = $user->gender === 'male' ? 'female' : ($user->gender === 'female' ? 'male' : null);

        $query = Profile::with(['user', 'primaryPhoto'])
            ->where('user_id', '!=', $user->id)
            ->whereHas('user', function ($q) use ($oppositeGender) {
                $q->where('is_active', true);
                if ($oppositeGender) {
                    $q->where('gender', $oppositeGender);
                }
            });

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }
        if ($request->filled('sect')) {
            $query->where('sect_madhhab', $request->sect);
        }

        $profiles = $query->paginate(15);

        if ($user->profile) {
            $profiles->getCollection()->transform(function ($p) use ($user) {
                $p->compatibility_score = $this->matchingEngine->computeCompatibility($user->profile, $p);

                return $p;
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $profiles,
        ]);
    }

    public function dailyMatches(Request $request): JsonResponse
    {
        $user = $request->user();
        $matches = $this->matchingEngine->getDailyMatches($user, 10);

        return response()->json([
            'status' => 'success',
            'data' => $matches,
        ]);
    }

    public function show(Request $request, int $userId): JsonResponse
    {
        $user = $request->user();
        $targetUser = User::with(['profile.photos', 'profile.primaryPhoto'])->findOrFail($userId);

        if (! $this->quotaService->canViewProfile($user, $targetUser->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Daily profile view limit reached. Upgrade to Premium for unlimited views.',
            ], 403);
        }

        $this->quotaService->recordView($user, $targetUser->id);

        $compatibility = null;
        if ($user->profile && $targetUser->profile) {
            $compatibility = $this->matchingEngine->computeCompatibility($user->profile, $targetUser->profile);
        }

        $isPhotoVisible = $targetUser->profile?->isPhotoVisibleTo($user);

        return response()->json([
            'status' => 'success',
            'user' => $targetUser,
            'compatibility_score' => $compatibility,
            'is_photo_visible' => $isPhotoVisible,
        ]);
    }
}
