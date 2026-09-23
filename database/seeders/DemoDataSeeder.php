<?php

namespace Database\Seeders;

use App\Models\Photo;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WaliLink;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlatformSettingSeeder::class);
        $this->call(SuperAdminSeeder::class);

        $defaultPassword = Hash::make('password');

        // 1. Moderator Account
        $moderator = User::create([
            'name' => 'Amina Siddiqui (Moderator)',
            'email' => 'moderator@nikahconnect.test',
            'phone' => '+15550002222',
            'password' => $defaultPassword,
            'role' => 'moderator',
            'permissions' => ['manage_verifications', 'manage_reports', 'view_audit_logs'],
            'gender' => 'female',
            'dob' => '1993-08-20',
            'marital_status' => 'never_married',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 2. Wali (Guardian) Account
        $wali = User::create([
            'name' => 'Dr. Tariq Al-Mansoor (Wali)',
            'email' => 'wali.father@nikahconnect.test',
            'phone' => '+15550003333',
            'password' => $defaultPassword,
            'role' => 'wali',
            'gender' => 'male',
            'dob' => '1965-03-10',
            'marital_status' => 'never_married',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 3. Primary Seeker Groom (Male)
        $groom = User::create([
            'name' => 'Zayd Al-Hassan',
            'email' => 'seeker.groom@nikahconnect.test',
            'phone' => '+15550004444',
            'password' => $defaultPassword,
            'role' => 'seeker',
            'gender' => 'male',
            'dob' => '1997-04-12',
            'marital_status' => 'never_married',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Subscription::create([
            'user_id' => $groom->id,
            'plan' => 'premium',
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $groomProfile = Profile::create([
            'user_id' => $groom->id,
            'height_cm' => 180,
            'education_level' => 'Master of Science in Computer Science',
            'profession' => 'Senior Cloud Architect',
            'employment_type' => 'Full-time',
            'annual_income_range' => '$100,000 - $150,000',
            'city' => 'London',
            'state' => 'Greater London',
            'country' => 'United Kingdom',
            'mother_tongue' => 'English',
            'citizenship' => 'British',
            'bio' => 'Practicing Muslim committed to deen, continuous personal growth, and family values. Seeking a pious, compassionate partner with whom to build a peaceful household rooted in sunnah.',
            'sect_madhhab' => 'Sunni - Hanafi',
            'prayer_frequency' => '5x_daily',
            'beard_practice' => 'full_sunnah_beard',
            'quran_knowledge' => 'fluent_reciter',
            'mosque_attendance' => 'daily',
            'halal_dietary_adherence' => 'strictly_halal',
            'polygamy_opinion' => 'against',
            'desired_family_structure' => 'independent',
            'parents_status' => 'Both parents retired',
            'parents_occupation' => 'Civil Engineer & Teacher',
            'siblings_count' => 2,
            'family_religiosity' => 'very_practicing',
            'wali_required' => false,
            'completeness_percentage' => 95,
        ]);

        Photo::create([
            'profile_id' => $groomProfile->id,
            'file_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&auto=format&fit=crop&q=80',
            'is_primary' => true,
            'is_blurred' => false,
            'moderation_status' => 'approved',
        ]);

        // 4. Primary Seeker Bride (Female) with Wali Linked
        $bride = User::create([
            'name' => 'Maryam Al-Mansoor',
            'email' => 'seeker.bride@nikahconnect.test',
            'phone' => '+15550005555',
            'password' => $defaultPassword,
            'role' => 'seeker',
            'gender' => 'female',
            'dob' => '2000-09-18',
            'marital_status' => 'never_married',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Subscription::create([
            'user_id' => $bride->id,
            'plan' => 'free',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $brideProfile = Profile::create([
            'user_id' => $bride->id,
            'height_cm' => 165,
            'education_level' => 'Doctor of Pharmacy (PharmD)',
            'profession' => 'Clinical Pharmacist',
            'employment_type' => 'Full-time',
            'annual_income_range' => '$70,000 - $90,000',
            'city' => 'London',
            'state' => 'Greater London',
            'country' => 'United Kingdom',
            'mother_tongue' => 'English',
            'citizenship' => 'British',
            'bio' => 'Alhamdulillah, family-oriented, grounded in daily prayer and Islamic values. Looking for a practicing brother who prioritizes Allah and values respectful communication. My father is involved as my Wali.',
            'sect_madhhab' => 'Sunni - Hanafi',
            'prayer_frequency' => '5x_daily',
            'hijab_niqab_practice' => 'hijab',
            'quran_knowledge' => 'hafiz',
            'mosque_attendance' => 'weekly_jummah',
            'halal_dietary_adherence' => 'strictly_halal',
            'polygamy_opinion' => 'against',
            'desired_family_structure' => 'independent',
            'parents_status' => 'Father Physician, Mother Homemaker',
            'parents_occupation' => 'Physician',
            'siblings_count' => 1,
            'family_religiosity' => 'very_practicing',
            'wali_required' => true,
            'completeness_percentage' => 100,
        ]);

        Photo::create([
            'profile_id' => $brideProfile->id,
            'file_path' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80',
            'is_primary' => true,
            'is_blurred' => true,
            'moderation_status' => 'approved',
        ]);

        WaliLink::create([
            'seeker_user_id' => $bride->id,
            'wali_user_id' => $wali->id,
            'wali_name' => $wali->name,
            'wali_email' => $wali->email,
            'wali_phone' => $wali->phone,
            'relationship_type' => 'father',
            'permission_level' => 'approve_required',
            'status' => 'active',
        ]);
    }
}
