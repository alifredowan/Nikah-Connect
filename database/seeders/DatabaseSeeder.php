<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Interest;
use App\Models\Message;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Models\WaliLink;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlatformSettingSeeder::class);

        $defaultPassword = Hash::make('password');

        // 1. Admin Account
        $admin = User::create([
            'name' => 'Farhan Qureshi (Admin)',
            'email' => 'admin@nikahconnect.test',
            'phone' => '+15550001111',
            'password' => $defaultPassword,
            'role' => 'admin',
            'gender' => 'male',
            'dob' => '1990-05-14',
            'marital_status' => 'never_married',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 2. Moderator Account
        $moderator = User::create([
            'name' => 'Amina Siddiqui (Moderator)',
            'email' => 'moderator@nikahconnect.test',
            'phone' => '+15550002222',
            'password' => $defaultPassword,
            'role' => 'moderator',
            'gender' => 'female',
            'dob' => '1993-08-20',
            'marital_status' => 'never_married',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 3. Wali (Guardian) Account
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

        // 4. Primary Seeker Groom (Male)
        $groom = User::create([
            'name' => 'Zayd Al-Hassan',
            'email' => 'seeker.groom@nikahconnect.test',
            'phone' => '+15550004444',
            'password' => $defaultPassword,
            'role' => 'seeker',
            'gender' => 'male',
            'dob' => '1997-04-12', // 29 years
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
            'partner_preferences' => [
                'age_min' => 22,
                'age_max' => 28,
                'preferred_sect' => 'Sunni - Hanafi',
                'preferred_location' => 'United Kingdom',
                'preferred_education' => 'Bachelor or above',
            ],
            'completeness_percentage' => 95,
        ]);

        Photo::create([
            'profile_id' => $groomProfile->id,
            'file_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&auto=format&fit=crop&q=80',
            'is_primary' => true,
            'is_blurred' => false,
            'moderation_status' => 'approved',
        ]);

        // 5. Primary Seeker Bride (Female) with Wali Linked
        $bride = User::create([
            'name' => 'Maryam Al-Mansoor',
            'email' => 'seeker.bride@nikahconnect.test',
            'phone' => '+15550005555',
            'password' => $defaultPassword,
            'role' => 'seeker',
            'gender' => 'female',
            'dob' => '2000-09-18', // 25 years
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
            'partner_preferences' => [
                'age_min' => 26,
                'age_max' => 32,
                'preferred_sect' => 'Sunni - Hanafi',
                'preferred_location' => 'United Kingdom',
                'preferred_education' => 'University Graduate',
            ],
            'completeness_percentage' => 100,
        ]);

        Photo::create([
            'profile_id' => $brideProfile->id,
            'file_path' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80',
            'is_primary' => true,
            'is_blurred' => true, // default blurred per FR-2.2
            'moderation_status' => 'approved',
        ]);

        // Link Bride to Wali with approve_required permission
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

        // 6. Additional diverse candidates
        $candidatesData = [
            [
                'name' => 'Fatima Zahra Khan',
                'gender' => 'female',
                'dob' => '2001-02-15',
                'marital_status' => 'never_married',
                'city' => 'Milan',
                'country' => 'Italy',
                'education' => 'Bachelor in Economics',
                'profession' => 'Financial Analyst',
                'sect' => 'Sunni - Shafi\'i',
                'prayer' => '5x_daily',
                'hijab' => 'hijab',
                'diet' => 'strictly_halal',
                'bio' => 'Born and raised in Italy, passionate about Islamic finance, modest lifestyle, and community volunteering.',
                'photo' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Dr. Bilal Rahman',
                'gender' => 'male',
                'dob' => '1995-11-23',
                'marital_status' => 'never_married',
                'city' => 'Toronto',
                'country' => 'Canada',
                'education' => 'Doctor of Medicine (MD)',
                'profession' => 'Resident Physician',
                'sect' => 'Sunni - Hanafi',
                'prayer' => '5x_daily',
                'beard' => 'trimmed_beard',
                'diet' => 'strictly_halal',
                'bio' => 'Balancing hospital duties with Islamic study. Enjoys hiking, reading history, and seeking an uplifting companion.',
                'photo' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Safiyya Bouzid',
                'gender' => 'female',
                'dob' => '1998-07-19',
                'marital_status' => 'never_married',
                'city' => 'Paris',
                'country' => 'France',
                'education' => 'Master in Data Science',
                'profession' => 'AI Researcher',
                'sect' => 'Sunni - Maliki',
                'prayer' => '5x_daily',
                'hijab' => 'hijab',
                'diet' => 'strictly_halal',
                'bio' => 'Maliki background, loves intellectual discussions, nature walks, and learning Arabic calligraphy.',
                'photo' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Omar Farooq Chowdhury',
                'gender' => 'male',
                'dob' => '1994-01-30',
                'marital_status' => 'never_married',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'education' => 'BSc in Electrical Engineering',
                'profession' => 'Renewable Energy Consultant',
                'sect' => 'Sunni - Hanafi',
                'prayer' => '5x_daily',
                'beard' => 'full_sunnah_beard',
                'diet' => 'strictly_halal',
                'bio' => 'Committed to honesty, Islamic ethics in business, and simple living. Looking for a partner to share life\'s blessings.',
                'photo' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=400&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Aisha Nur Binte Yusuf',
                'gender' => 'female',
                'dob' => '1999-12-05',
                'marital_status' => 'never_married',
                'city' => 'Dubai',
                'country' => 'United Arab Emirates',
                'education' => 'Bachelor of Architecture',
                'profession' => 'Architectural Designer',
                'sect' => 'Sunni - Hanbali',
                'prayer' => '5x_daily',
                'hijab' => 'niqab',
                'diet' => 'strictly_halal',
                'bio' => 'Observes niqab, creative, values traditional Islamic family structure. Guardian will oversee all contact.',
                'photo' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Hamza Tariq',
                'gender' => 'male',
                'dob' => '1996-08-14',
                'marital_status' => 'never_married',
                'city' => 'Manchester',
                'country' => 'United Kingdom',
                'education' => 'BSc in Accounting',
                'profession' => 'Chartered Accountant',
                'sect' => 'Sunni - Hanafi',
                'prayer' => '5x_daily',
                'beard' => 'trimmed_beard',
                'diet' => 'strictly_halal',
                'bio' => 'Outgoing, values good humor, sports, and maintaining strong family ties. Active in youth mentorship at the local mosque.',
                'photo' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($candidatesData as $idx => $c) {
            $user = User::create([
                'name' => $c['name'],
                'email' => "candidate{$idx}@nikahconnect.test",
                'phone' => '+1555111'.str_pad($idx, 4, '0', STR_PAD_LEFT),
                'password' => $defaultPassword,
                'role' => 'seeker',
                'gender' => $c['gender'],
                'dob' => $c['dob'],
                'marital_status' => $c['marital_status'],
                'is_verified' => ($idx % 2 === 0),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            Subscription::create([
                'user_id' => $user->id,
                'plan' => ($idx === 1 ? 'premium_plus' : ($idx === 2 ? 'premium' : 'free')),
                'status' => 'active',
                'starts_at' => now(),
            ]);

            $p = Profile::create([
                'user_id' => $user->id,
                'height_cm' => $c['gender'] === 'male' ? 178 : 162,
                'education_level' => $c['education'],
                'profession' => $c['profession'],
                'employment_type' => 'Full-time',
                'city' => $c['city'],
                'country' => $c['country'],
                'bio' => $c['bio'],
                'sect_madhhab' => $c['sect'],
                'prayer_frequency' => $c['prayer'],
                'hijab_niqab_practice' => $c['hijab'] ?? null,
                'beard_practice' => $c['beard'] ?? null,
                'halal_dietary_adherence' => $c['diet'],
                'quran_knowledge' => 'fluent_reciter',
                'wali_required' => ($c['gender'] === 'female'),
                'completeness_percentage' => 90,
            ]);

            Photo::create([
                'profile_id' => $p->id,
                'file_path' => $c['photo'],
                'is_primary' => true,
                'is_blurred' => true, // default blurred
                'moderation_status' => 'approved',
            ]);
        }

        // 7. Seed Sample Interaction & Chaperoned Conversation between Groom & Bride
        $interest = Interest::create([
            'sender_id' => $groom->id,
            'recipient_id' => $bride->id,
            'status' => 'accepted',
            'wali_approval_status' => 'approved',
            'wali_id' => $wali->id,
            'message_note' => 'As-salamu alaykum, I reviewed your profile and appreciate your dedication to deen and family. I would be honored to introduce myself to your Wali.',
            'responded_at' => now()->subDay(),
        ]);

        $conversation = Conversation::create([
            'interest_id' => $interest->id,
            'status' => 'active',
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $groom->id,
            'role' => 'seeker',
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $bride->id,
            'role' => 'seeker',
        ]);

        // Add Wali as chaperone observer (FR-4.3, 6.2)
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $wali->id,
            'role' => 'wali_chaperone',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $groom->id,
            'body' => 'Wa alaykum as-salam. Thank you for accepting my interest. Respected Dr. Tariq, it is an honor to have you observing our conversation.',
            'created_at' => now()->subHours(5),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $wali->id,
            'body' => 'As-salamu alaykum Zayd. Welcome. As Maryam\'s father, I oversee all communications to ensure our discussions remain halal and focused on future marriage compatibility. Please tell us about your daily Islamic routine and family values.',
            'created_at' => now()->subHours(4),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $bride->id,
            'body' => 'Wa alaykum as-salam. Thank you for your thoughtful message. I would be happy to discuss further within our family circle.',
            'created_at' => now()->subHours(2),
        ]);

        // Seed pending verification request for admin moderation queue demo
        VerificationRequest::create([
            'user_id' => $groom->id,
            'document_type' => 'passport',
            'document_path' => 'demo/passport_sample.png',
            'status' => 'approved',
            'reviewer_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // A pending verification request from candidate 1
        $candUser = User::where('email', 'candidate1@nikahconnect.test')->first();
        if ($candUser) {
            VerificationRequest::create([
                'user_id' => $candUser->id,
                'document_type' => 'national_id',
                'document_path' => 'demo/id_card_sample.png',
                'selfie_path' => 'demo/selfie_sample.png',
                'status' => 'pending',
            ]);
        }
    }
}
