<?php

namespace Tests\Feature;

use App\Models\Interest;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\User;
use App\Models\WaliLink;
use App\Services\WaliWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MatrimonialFeatureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_landing_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Nikah');
        $response->assertSee('Connect');
    }

    public function test_registration_rejects_under_18_candidates(): void
    {
        // Under 18 (e.g. 17 years old)
        $underageDob = Carbon::now()->subYears(17)->toDateString();

        $response = $this->post('/register', [
            'name' => 'Underage Applicant',
            'email' => 'underage@test.com',
            'gender' => 'male',
            'dob' => $underageDob,
            'marital_status' => 'never_married',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('dob');
    }

    public function test_registration_accepts_adult_candidates(): void
    {
        // Exactly 22 years old
        $adultDob = Carbon::now()->subYears(22)->toDateString();

        $response = $this->post('/register', [
            'name' => 'Adult Applicant',
            'email' => 'adult@test.com',
            'gender' => 'female',
            'dob' => $adultDob,
            'marital_status' => 'never_married',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('users', ['email' => 'adult@test.com']);
    }

    public function test_wali_approval_workflow_unlocks_conversation(): void
    {
        $wali = User::create([
            'name' => 'Wali Father',
            'email' => 'wali.test@test.com',
            'password' => bcrypt('password'),
            'role' => 'wali',
            'gender' => 'male',
            'is_active' => true,
        ]);

        $bride = User::create([
            'name' => 'Bride Candidate',
            'email' => 'bride.test@test.com',
            'password' => bcrypt('password'),
            'role' => 'seeker',
            'gender' => 'female',
            'is_active' => true,
        ]);
        $brideProfile = Profile::create(['user_id' => $bride->id, 'wali_required' => true]);

        $groom = User::create([
            'name' => 'Groom Candidate',
            'email' => 'groom.test@test.com',
            'password' => bcrypt('password'),
            'role' => 'seeker',
            'gender' => 'male',
            'is_active' => true,
        ]);

        WaliLink::create([
            'seeker_user_id' => $bride->id,
            'wali_user_id' => $wali->id,
            'relationship_type' => 'father',
            'permission_level' => 'approve_required',
            'status' => 'active',
        ]);

        $waliService = app(WaliWorkflowService::class);
        $this->assertTrue($waliService->requiresWaliApproval($bride));

        // Create interest from groom to bride
        $interest = Interest::create([
            'sender_id' => $groom->id,
            'recipient_id' => $bride->id,
            'status' => 'accepted', // Bride accepts
            'wali_approval_status' => 'pending', // But awaiting wali!
        ]);

        $this->assertFalse($interest->canChatUnlock());

        // Wali approves!
        $waliService->approveByWali($interest, $wali);

        $interest->refresh();
        $this->assertSame('approved', $interest->wali_approval_status);
        $this->assertTrue($interest->canChatUnlock());
        $this->assertNotNull($interest->conversation);
    }

    public function test_photo_is_server_side_protected_for_unauthorized_guests(): void
    {
        $bride = User::create([
            'name' => 'Test Bride',
            'email' => 'photo.test@nikahconnect.test',
            'password' => bcrypt('password'),
            'role' => 'seeker',
            'gender' => 'female',
            'is_active' => true,
        ]);
        $profile = Profile::create(['user_id' => $bride->id, 'wali_required' => true]);
        $primaryPhoto = Photo::create([
            'profile_id' => $profile->id,
            'file_path' => 'https://example.com/test.jpg',
            'is_primary' => true,
            'is_blurred' => true,
            'moderation_status' => 'approved',
        ]);

        // Guest requesting photo endpoint directly
        $response = $this->get(route('photos.view', $primaryPhoto->id));
        $response->assertStatus(200);

        // Unauthorized guest must receive image content (SVG or blurred JPEG), NOT a redirect to raw file
        $this->assertFalse($response->isRedirection());
        $contentType = $response->headers->get('Content-Type');
        $this->assertTrue(in_array($contentType, ['image/svg+xml', 'image/jpeg', 'image/png']));
    }

    public function test_registration_rejects_duplicate_email_case_insensitively_and_with_spaces(): void
    {
        $adultDob = Carbon::now()->subYears(25)->toDateString();

        User::create([
            'name' => 'Existing User',
            'email' => 'unique.check@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'seeker',
            'gender' => 'male',
            'dob' => $adultDob,
            'marital_status' => 'never_married',
            'is_active' => true,
        ]);

        // Attempt registering with uppercase characters and whitespace
        $response = $this->post('/register', [
            'name' => 'Duplicate Candidate',
            'email' => '  UnIqUe.ChEcK@tEsT.cOm  ',
            'gender' => 'female',
            'dob' => $adultDob,
            'marital_status' => 'never_married',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertSame(
            'An account with this email address already exists. Please sign in or use forgot password.',
            session('errors')->first('email')
        );

        // Ensure database only contains 1 user for this email
        $this->assertSame(1, User::whereRaw('LOWER(email) = ?', ['unique.check@test.com'])->count());
    }

    public function test_login_works_case_insensitively_and_with_spaces(): void
    {
        $adultDob = Carbon::now()->subYears(25)->toDateString();

        $user = User::create([
            'name' => 'Case Sensitive User',
            'email' => 'case.login@test.com',
            'password' => bcrypt('SecretPassword123!'),
            'role' => 'seeker',
            'gender' => 'male',
            'dob' => $adultDob,
            'marital_status' => 'never_married',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => '  CaSe.LoGiN@TeSt.CoM  ',
            'password' => 'SecretPassword123!',
        ]);

        $response->assertRedirect(route('discovery.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_discovery_strictly_shows_opposite_gender_and_excludes_staff_and_wali(): void
    {
        $dob = Carbon::now()->subYears(25)->toDateString();

        // 1. Male seeker (current logged-in user)
        $maleSeeker = User::create([
            'name' => 'Groom Seeker',
            'email' => 'groom.discover@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'seeker',
            'gender' => 'male',
            'dob' => $dob,
            'marital_status' => 'never_married',
            'is_active' => true,
        ]);
        Profile::create(['user_id' => $maleSeeker->id, 'sect_madhhab' => 'Sunni Hanafi']);

        // 2. Female seeker (should appear for male seeker)
        $femaleSeeker = User::create([
            'name' => 'Bride Candidate',
            'email' => 'bride.discover@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'seeker',
            'gender' => 'female',
            'dob' => $dob,
            'marital_status' => 'never_married',
            'is_active' => true,
        ]);
        Profile::create(['user_id' => $femaleSeeker->id, 'sect_madhhab' => 'Sunni Hanafi']);

        // 3. Another Male seeker (must NOT appear for male seeker)
        $otherMaleSeeker = User::create([
            'name' => 'Other Male Seeker',
            'email' => 'other.male@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'seeker',
            'gender' => 'male',
            'dob' => $dob,
            'marital_status' => 'never_married',
            'is_active' => true,
        ]);
        Profile::create(['user_id' => $otherMaleSeeker->id, 'sect_madhhab' => 'Sunni Hanafi']);

        // 4. Super Admin (must NEVER appear)
        $superAdmin = User::create([
            'name' => 'Admin Boss',
            'email' => 'admin.boss@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'super_admin',
            'gender' => 'female',
            'dob' => $dob,
            'is_active' => true,
        ]);
        Profile::create(['user_id' => $superAdmin->id]);

        // 5. Moderator (must NEVER appear)
        $moderator = User::create([
            'name' => 'Mod Staff',
            'email' => 'mod.staff@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'moderator',
            'gender' => 'female',
            'dob' => $dob,
            'is_active' => true,
        ]);
        Profile::create(['user_id' => $moderator->id]);

        // 6. Wali Guardian (must NEVER appear)
        $wali = User::create([
            'name' => 'Wali Guardian',
            'email' => 'wali.guardian@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'wali',
            'gender' => 'female',
            'dob' => $dob,
            'is_active' => true,
        ]);
        Profile::create(['user_id' => $wali->id]);

        // Act: Male seeker visits /discover
        $response = $this->actingAs($maleSeeker)->get(route('discovery.index'));

        $response->assertOk();
        $discoveredMaleView = $response->viewData('profiles')->pluck('user.name');
        $this->assertTrue($discoveredMaleView->contains('Bride Candidate'));
        $this->assertFalse($discoveredMaleView->contains('Other Male Seeker'));
        $this->assertFalse($discoveredMaleView->contains('Groom Seeker'));
        $this->assertFalse($discoveredMaleView->contains('Admin Boss'));
        $this->assertFalse($discoveredMaleView->contains('Mod Staff'));
        $this->assertFalse($discoveredMaleView->contains('Wali Guardian'));

        // Act: Female seeker visits /discover -> only male seekers must appear
        $femaleResponse = $this->actingAs($femaleSeeker)->get(route('discovery.index'));
        $femaleResponse->assertOk();
        $discoveredFemaleView = $femaleResponse->viewData('profiles')->pluck('user.name');
        $this->assertTrue($discoveredFemaleView->contains('Groom Seeker'));
        $this->assertTrue($discoveredFemaleView->contains('Other Male Seeker'));
        $this->assertFalse($discoveredFemaleView->contains('Bride Candidate'));
        $this->assertFalse($discoveredFemaleView->contains('Admin Boss'));
        $this->assertFalse($discoveredFemaleView->contains('Mod Staff'));
        $this->assertFalse($discoveredFemaleView->contains('Wali Guardian'));

        // Act: Seeker cannot view or send interest to admin or same-gender candidate
        $showAdminResponse = $this->actingAs($maleSeeker)->get(route('discovery.show', $superAdmin->id));
        $showAdminResponse->assertRedirect(route('discovery.index'));

        $showSameGenderResponse = $this->actingAs($maleSeeker)->get(route('discovery.show', $otherMaleSeeker->id));
        $showSameGenderResponse->assertRedirect(route('discovery.index'));

        $interestToAdminResponse = $this->actingAs($maleSeeker)->post(route('interests.send', $superAdmin->id));
        $interestToAdminResponse->assertSessionHas('error');

        $interestToSameGenderResponse = $this->actingAs($maleSeeker)->post(route('interests.send', $otherMaleSeeker->id));
        $interestToSameGenderResponse->assertSessionHas('error');
    }
}
