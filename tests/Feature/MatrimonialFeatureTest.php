<?php

namespace Tests\Feature;

use App\Models\Interest;
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
        $bride = User::where('email', 'seeker.bride@nikahconnect.test')->first();
        $this->assertNotNull($bride);
        $primaryPhoto = $bride->profile?->primaryPhoto;
        $this->assertNotNull($primaryPhoto);

        // Guest requesting photo endpoint directly
        $response = $this->get(route('photos.view', $primaryPhoto->id));
        $response->assertStatus(200);

        // Unauthorized guest must receive image content (SVG or blurred JPEG), NOT a redirect to raw file
        $this->assertFalse($response->isRedirection());
        $contentType = $response->headers->get('Content-Type');
        $this->assertTrue(in_array($contentType, ['image/svg+xml', 'image/jpeg', 'image/png']));
    }
}
