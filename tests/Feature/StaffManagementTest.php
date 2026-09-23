<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_register_as_wali(): void
    {
        $adultDob = Carbon::now()->subYears(45)->toDateString();

        $response = $this->post('/register', [
            'role' => 'wali',
            'name' => 'Al-Mansoor Guardian',
            'email' => 'wali.guardian@test.com',
            'gender' => 'male',
            'dob' => $adultDob,
            'relationship_type' => 'father',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('wali.link'));
        $this->assertDatabaseHas('users', [
            'email' => 'wali.guardian@test.com',
            'role' => 'wali',
        ]);
    }

    public function test_super_admin_can_access_staff_management(): void
    {
        $superAdmin = User::create([
            'name' => 'Head Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'gender' => 'male',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.staff'));
        $response->assertStatus(200);
        $response->assertSee('Staff &amp; Access Control', false);
    }

    public function test_moderator_cannot_access_staff_management(): void
    {
        $moderator = User::create([
            'name' => 'Regular Moderator',
            'email' => 'mod@test.com',
            'password' => bcrypt('password'),
            'role' => 'moderator',
            'permissions' => ['manage_verifications'],
            'gender' => 'male',
            'is_active' => true,
        ]);

        $response = $this->actingAs($moderator)->get(route('admin.staff'));
        $response->assertStatus(403);
    }

    public function test_moderator_permissions_are_enforced(): void
    {
        // Moderator with only manage_verifications
        $moderator = User::create([
            'name' => 'Verification Moderator',
            'email' => 'verifier@test.com',
            'password' => bcrypt('password'),
            'role' => 'moderator',
            'permissions' => ['manage_verifications'],
            'gender' => 'female',
            'is_active' => true,
        ]);

        // Can access verifications
        $allowedResponse = $this->actingAs($moderator)->get(route('admin.verifications'));
        $allowedResponse->assertStatus(200);

        // Forbidden on reports
        $deniedResponse = $this->actingAs($moderator)->get(route('admin.reports'));
        $deniedResponse->assertStatus(403);
    }

    public function test_super_admin_can_create_new_moderator_with_permissions(): void
    {
        $superAdmin = User::create([
            'name' => 'Primary Super Admin',
            'email' => 'head@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'gender' => 'male',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.staff.store'), [
            'name' => 'Newly Created Moderator',
            'email' => 'newmod@test.com',
            'role' => 'moderator',
            'password' => 'SecurePass123!',
            'permissions' => ['manage_verifications', 'manage_reports'],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'email' => 'newmod@test.com',
            'role' => 'moderator',
        ]);

        $createdMod = User::where('email', 'newmod@test.com')->first();
        $this->assertTrue($createdMod->hasPermission('manage_verifications'));
        $this->assertTrue($createdMod->hasPermission('manage_reports'));
        $this->assertFalse($createdMod->hasPermission('manage_settings'));
    }
}
