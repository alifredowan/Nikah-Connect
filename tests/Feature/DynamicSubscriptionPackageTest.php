<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class DynamicSubscriptionPackageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        SubscriptionPlan::updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free Seeker',
                'monthly_price' => 0.00,
                'annual_price' => 0.00,
                'daily_profile_views' => 10,
                'daily_interests' => 5,
                'is_active' => true,
            ]
        );
    }

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'email' => 'admin.'.Str::random(8).'@example.com',
            'role' => 'super_admin',
            'permissions' => ['manage_settings', 'manage_verifications', 'manage_reports', 'view_audit_logs'],
            'is_verified' => true,
            'is_active' => true,
        ]);
    }

    private function createSeeker(?string $email = null): User
    {
        return User::factory()->create([
            'email' => $email ?? ('seeker.'.Str::random(8).'@example.com'),
            'role' => 'seeker',
            'is_verified' => true,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_view_packages_page(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin)->get(route('admin.packages.index'));

        $response->assertStatus(200)
            ->assertSee('Dynamic Subscription Packages')
            ->assertSee('Create New Package');
    }

    public function test_super_admin_can_create_dynamic_package(): void
    {
        $admin = $this->createSuperAdmin();
        $slug = 'pro-'.strtolower(Str::random(6));

        $response = $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'Pro Seeker',
            'slug' => $slug,
            'description' => 'Dedicated package for ambitious marriage seekers.',
            'monthly_price' => 29.99,
            'annual_price' => 249.99,
            'daily_profile_views' => -1,
            'daily_interests' => -1,
            'advanced_filters' => '1',
            'profile_boost' => '1',
            'see_who_viewed' => '1',
            'dedicated_advisor' => '0',
            'badge_text' => 'Pro Choice',
            'features' => "Unlimited Profile Views\nPriority Match Engine\nFree Profile Boost",
            'is_active' => '1',
            'is_popular' => '1',
            'sort_order' => 4,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subscription_plans', [
            'slug' => $slug,
            'name' => 'Pro Seeker',
            'monthly_price' => 29.99,
            'annual_price' => 249.99,
            'profile_boost' => true,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_update_dynamic_package(): void
    {
        $admin = $this->createSuperAdmin();
        $slug1 = 'starter-'.strtolower(Str::random(6));
        $slug2 = 'starter-plus-'.strtolower(Str::random(6));

        $package = SubscriptionPlan::create([
            'name' => 'Starter Pack',
            'slug' => $slug1,
            'monthly_price' => 9.99,
            'annual_price' => 89.99,
            'daily_profile_views' => 25,
            'daily_interests' => 15,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.packages.update', $package->id), [
            'name' => 'Starter Plus',
            'slug' => $slug2,
            'description' => 'Updated description.',
            'monthly_price' => 12.99,
            'annual_price' => 99.99,
            'daily_profile_views' => 50,
            'daily_interests' => 25,
            'advanced_filters' => '1',
            'is_active' => '1',
            'sort_order' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subscription_plans', [
            'id' => $package->id,
            'name' => 'Starter Plus',
            'slug' => $slug2,
            'monthly_price' => 12.99,
            'daily_profile_views' => 50,
        ]);
    }

    public function test_super_admin_can_toggle_package_status(): void
    {
        $admin = $this->createSuperAdmin();
        $slug = 'silver-'.Str::random(6);

        $package = SubscriptionPlan::create([
            'name' => 'Silver Tier',
            'slug' => $slug,
            'monthly_price' => 14.99,
            'annual_price' => 120.00,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.packages.toggle-status', $package->id));
        $this->assertFalse($package->fresh()->is_active);

        $this->actingAs($admin)->post(route('admin.packages.toggle-status', $package->id));
        $this->assertTrue($package->fresh()->is_active);
    }

    public function test_super_admin_can_generate_targeted_promo_code(): void
    {
        $admin = $this->createSuperAdmin();
        $code = 'SPEC'.strtoupper(Str::random(6));

        $response = $this->actingAs($admin)->post(route('admin.promo-codes.store'), [
            'code' => $code,
            'discount_percentage' => 50,
            'plan_slug' => 'premium',
            'allowed_emails' => 'special.vip@example.com, another.vip@example.com',
            'description' => 'Special 50% discount for VIP seeker',
            'max_uses' => 20,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('discount_codes', [
            'code' => $code,
            'discount_percentage' => 50,
            'plan_slug' => 'premium',
            'allowed_emails' => 'special.vip@example.com, another.vip@example.com',
            'max_uses' => 20,
            'is_active' => true,
        ]);
    }

    public function test_special_user_can_avail_extra_discount_on_pro_package(): void
    {
        $proSlug = 'pro-'.Str::random(6);
        $proPlan = SubscriptionPlan::create([
            'name' => 'Pro Package',
            'slug' => $proSlug,
            'monthly_price' => 50.00,
            'annual_price' => 400.00,
            'is_active' => true,
        ]);

        $specialEmail = 'special.'.Str::random(6).'@example.com';
        $specialUser = $this->createSeeker($specialEmail);

        $promoCode = 'VIP'.strtoupper(Str::random(6));
        DiscountCode::create([
            'code' => $promoCode,
            'discount_percentage' => 40, // 40% off $50.00 = $30.00
            'plan_slug' => $proSlug,
            'allowed_emails' => $specialEmail,
            'max_uses' => 10,
            'times_used' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($specialUser)->post(route('subscription.process'), [
            'plan' => $proSlug,
            'billing_cycle' => 'monthly',
            'promo_code' => $promoCode,
        ]);

        $response->assertRedirect(route('subscription.pricing'));
        $response->assertSessionHas('success');

        $subscription = Subscription::where('user_id', $specialUser->id)->latest()->first();
        $this->assertNotNull($subscription);
        $this->assertEquals($proSlug, $subscription->plan);
        $this->assertEquals(30.00, (float) $subscription->amount_paid);

        $this->assertEquals(1, DiscountCode::where('code', $promoCode)->first()->times_used);
    }

    public function test_non_special_user_is_blocked_from_using_special_promo_code(): void
    {
        $proSlug = 'pro-'.Str::random(6);
        SubscriptionPlan::create([
            'name' => 'Pro Package',
            'slug' => $proSlug,
            'monthly_price' => 50.00,
            'annual_price' => 400.00,
            'is_active' => true,
        ]);

        $regularUser = $this->createSeeker();
        $promoCode = 'EXCL'.strtoupper(Str::random(6));

        DiscountCode::create([
            'code' => $promoCode,
            'discount_percentage' => 50,
            'plan_slug' => $proSlug,
            'allowed_emails' => 'exclusive.vip.person@example.com',
            'max_uses' => 10,
            'times_used' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($regularUser)->post(route('subscription.process'), [
            'plan' => $proSlug,
            'billing_cycle' => 'monthly',
            'promo_code' => $promoCode,
        ]);

        $response->assertSessionHasErrors('promo_code');
        $this->assertDatabaseMissing('subscriptions', [
            'user_id' => $regularUser->id,
            'plan' => $proSlug,
        ]);
    }

    public function test_promo_code_restricted_to_specific_package_cannot_be_used_on_other_packages(): void
    {
        $proSlug = 'pro-'.Str::random(6);
        SubscriptionPlan::create([
            'name' => 'Pro Package',
            'slug' => $proSlug,
            'monthly_price' => 50.00,
            'annual_price' => 400.00,
            'is_active' => true,
        ]);

        $basicSlug = 'basic-'.Str::random(6);
        SubscriptionPlan::create([
            'name' => 'Basic Tier',
            'slug' => $basicSlug,
            'monthly_price' => 20.00,
            'annual_price' => 150.00,
            'is_active' => true,
        ]);

        $user = $this->createSeeker();
        $promoCode = 'ONLYPRO'.strtoupper(Str::random(6));

        DiscountCode::create([
            'code' => $promoCode,
            'discount_percentage' => 30,
            'plan_slug' => $proSlug,
            'allowed_emails' => null, // open to all, but only for pro
            'max_uses' => 10,
            'times_used' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('subscription.process'), [
            'plan' => $basicSlug,
            'billing_cycle' => 'monthly',
            'promo_code' => $promoCode,
        ]);

        $response->assertSessionHasErrors('promo_code');
        $this->assertDatabaseMissing('subscriptions', [
            'user_id' => $user->id,
            'plan' => $basicSlug,
        ]);
    }

    public function test_ajax_promo_validation_endpoint(): void
    {
        $proSlug = 'pro-'.Str::random(6);
        SubscriptionPlan::create([
            'name' => 'Pro Package',
            'slug' => $proSlug,
            'monthly_price' => 100.00,
            'annual_price' => 800.00,
            'is_active' => true,
        ]);

        $vipEmail = 'vip.'.Str::random(6).'@example.com';
        $vip = $this->createSeeker($vipEmail);

        $promoCode = 'AJAX'.strtoupper(Str::random(6));
        DiscountCode::create([
            'code' => $promoCode,
            'discount_percentage' => 25,
            'plan_slug' => $proSlug,
            'allowed_emails' => $vipEmail,
            'max_uses' => 10,
            'is_active' => true,
        ]);

        // Valid test for eligible user
        $validResponse = $this->actingAs($vip)->postJson(route('subscription.validate-promo'), [
            'promo_code' => $promoCode,
            'plan' => $proSlug,
            'billing_cycle' => 'monthly',
        ]);

        $validResponse->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'discount_percentage' => 25,
                'discount_amount' => 25.00,
                'original_price' => 100.00,
                'final_price' => 75.00,
            ]);

        // Invalid test for non-special user
        $stranger = $this->createSeeker();
        $invalidResponse = $this->actingAs($stranger)->postJson(route('subscription.validate-promo'), [
            'promo_code' => $promoCode,
            'plan' => $proSlug,
            'billing_cycle' => 'monthly',
        ]);

        $invalidResponse->assertStatus(422)
            ->assertJson([
                'valid' => false,
            ]);
    }
}
