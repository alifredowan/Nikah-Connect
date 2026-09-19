<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Collection;

class MatchingEngineService
{
    /**
     * Compute compatibility percentage between two seeker profiles.
     * Weights religiosity fields higher than personal/lifestyle preferences (FR-3.2, FR-6.4).
     */
    public function computeCompatibility(Profile $p1, Profile $p2): int
    {
        $u1 = $p1->user;
        $u2 = $p2->user;

        // Opposites genders only for matrimonial
        if ($u1->gender && $u2->gender && $u1->gender === $u2->gender) {
            return 0;
        }

        $score = 0;
        $maxPossible = 100;

        // 1. Religious Alignment (Weight: 45 points)
        // Sect / Madhhab (20 points)
        if ($p1->sect_madhhab && $p2->sect_madhhab) {
            if (strtolower($p1->sect_madhhab) === strtolower($p2->sect_madhhab)) {
                $score += 20;
            } elseif (str_contains(strtolower($p1->sect_madhhab), 'sunni') && str_contains(strtolower($p2->sect_madhhab), 'sunni')) {
                $score += 15;
            }
        } else {
            $score += 10;
        }

        // Prayer frequency (15 points)
        if ($p1->prayer_frequency && $p2->prayer_frequency) {
            if ($p1->prayer_frequency === $p2->prayer_frequency) {
                $score += 15;
            } elseif ($p1->prayer_frequency === '5x_daily' || $p2->prayer_frequency === '5x_daily') {
                $score += 10;
            } else {
                $score += 5;
            }
        } else {
            $score += 8;
        }

        // Halal dietary adherence (5 points)
        if ($p1->halal_dietary_adherence && $p2->halal_dietary_adherence) {
            if ($p1->halal_dietary_adherence === $p2->halal_dietary_adherence) {
                $score += 5;
            } else {
                $score += 3;
            }
        } else {
            $score += 3;
        }

        // Quran knowledge / practice (5 points)
        if ($p1->quran_knowledge && $p2->quran_knowledge) {
            if ($p1->quran_knowledge === $p2->quran_knowledge) {
                $score += 5;
            } else {
                $score += 3;
            }
        } else {
            $score += 3;
        }

        // 2. Family & Values Alignment (Weight: 20 points)
        if ($p1->desired_family_structure && $p2->desired_family_structure) {
            if ($p1->desired_family_structure === $p2->desired_family_structure) {
                $score += 10;
            }
        } else {
            $score += 5;
        }

        if ($p1->family_religiosity && $p2->family_religiosity) {
            if ($p1->family_religiosity === $p2->family_religiosity) {
                $score += 10;
            } else {
                $score += 5;
            }
        } else {
            $score += 5;
        }

        // 3. Geographic / Location Alignment (Weight: 15 points)
        if ($p1->country && $p2->country) {
            if (strtolower($p1->country) === strtolower($p2->country)) {
                $score += 10;
                if ($p1->city && $p2->city && strtolower($p1->city) === strtolower($p2->city)) {
                    $score += 5;
                }
            }
        } else {
            $score += 5;
        }

        // 4. Education & Profession Alignment (Weight: 10 points)
        if ($p1->education_level && $p2->education_level) {
            if ($p1->education_level === $p2->education_level) {
                $score += 10;
            } else {
                $score += 5;
            }
        } else {
            $score += 5;
        }

        // 5. Age Preference Alignment (Weight: 10 points)
        $prefs1 = $p1->partner_preferences ?? [];
        $age2 = $u2->age;
        if ($age2 && ! empty($prefs1['age_min']) && ! empty($prefs1['age_max'])) {
            if ($age2 >= $prefs1['age_min'] && $age2 <= $prefs1['age_max']) {
                $score += 10;
            } elseif (abs($age2 - $prefs1['age_min']) <= 2 || abs($age2 - $prefs1['age_max']) <= 2) {
                $score += 5;
            }
        } else {
            $score += 7;
        }

        return min(100, max(15, $score));
    }

    /**
     * Get ranked match suggestions for a given user.
     */
    public function getDailyMatches(User $user, int $limit = 12): Collection
    {
        $profile = $user->profile;
        if (! $profile) {
            return collect();
        }

        $oppositeGender = $user->gender === 'male' ? 'female' : ($user->gender === 'female' ? 'male' : null);

        $query = Profile::with(['user', 'primaryPhoto', 'photos'])
            ->where('user_id', '!=', $user->id)
            ->whereHas('user', function ($q) use ($oppositeGender) {
                $q->where('is_active', true);
                if ($oppositeGender) {
                    $q->where('gender', $oppositeGender);
                }
            });

        $candidates = $query->get();

        return $candidates->map(function (Profile $candidate) use ($profile) {
            $candidate->compatibility_score = $this->computeCompatibility($profile, $candidate);

            return $candidate;
        })->sortByDesc('compatibility_score')->take($limit)->values();
    }
}
