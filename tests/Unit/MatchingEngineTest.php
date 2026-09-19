<?php

namespace Tests\Unit;

use App\Models\Profile;
use App\Models\User;
use App\Services\MatchingEngineService;
use Tests\TestCase;

class MatchingEngineTest extends TestCase
{
    protected MatchingEngineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MatchingEngineService;
    }

    public function test_it_returns_zero_for_same_gender_matrimonial(): void
    {
        $u1 = new User(['gender' => 'male']);
        $p1 = new Profile(['sect_madhhab' => 'Sunni - Hanafi']);
        $p1->setRelation('user', $u1);

        $u2 = new User(['gender' => 'male']);
        $p2 = new Profile(['sect_madhhab' => 'Sunni - Hanafi']);
        $p2->setRelation('user', $u2);

        $score = $this->service->computeCompatibility($p1, $p2);
        $this->assertSame(0, $score);
    }

    public function test_it_gives_high_score_for_matching_religiosity(): void
    {
        $groom = new User(['gender' => 'male', 'dob' => '1995-01-01']);
        $groomProfile = new Profile([
            'sect_madhhab' => 'Sunni - Hanafi',
            'prayer_frequency' => '5x_daily',
            'halal_dietary_adherence' => 'strictly_halal',
            'quran_knowledge' => 'fluent_reciter',
            'desired_family_structure' => 'independent',
            'family_religiosity' => 'very_practicing',
            'country' => 'United Kingdom',
            'city' => 'London',
            'education_level' => 'Master',
            'partner_preferences' => ['age_min' => 22, 'age_max' => 30],
        ]);
        $groomProfile->setRelation('user', $groom);

        $bride = new User(['gender' => 'female', 'dob' => '1998-01-01']);
        $brideProfile = new Profile([
            'sect_madhhab' => 'Sunni - Hanafi',
            'prayer_frequency' => '5x_daily',
            'halal_dietary_adherence' => 'strictly_halal',
            'quran_knowledge' => 'fluent_reciter',
            'desired_family_structure' => 'independent',
            'family_religiosity' => 'very_practicing',
            'country' => 'United Kingdom',
            'city' => 'London',
            'education_level' => 'Master',
            'partner_preferences' => ['age_min' => 28, 'age_max' => 35],
        ]);
        $brideProfile->setRelation('user', $bride);

        $score = $this->service->computeCompatibility($groomProfile, $brideProfile);

        $this->assertGreaterThanOrEqual(85, $score);
    }
}
