<?php

namespace Tests\Feature;

use App\Models\Bookmark;
use App\Models\Profile;
use App\Models\ProfileView;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\QuotaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProFeaturesTest extends TestCase
{
    use RefreshDatabase;

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

        SubscriptionPlan::updateOrCreate(
            ['slug' => 'premium'],
            [
                'name' => 'Seeker Premium',
                'monthly_price' => 19.99,
                'annual_price' => 159.99,
                'daily_profile_views' => -1,
                'daily_interests' => -1,
                'see_who_viewed' => true,
                'advanced_filters' => true,
                'profile_boost' => false,
                'is_active' => true,
            ]
        );
    }

    private function createSeeker(string $gender = 'male', bool $isPro = false): User
    {
        $user = User::factory()->create([
            'email' => 'user.'.Str::random(8).'@example.com',
            'role' => 'seeker',
            'gender' => $gender,
            'is_verified' => true,
            'is_active' => true,
        ]);

        Profile::create([
            'user_id' => $user->id,
            'city' => 'London',
            'country' => 'United Kingdom',
            'sect_madhhab' => 'Sunni - Hanafi',
            'prayer_frequency' => '5x_daily',
        ]);

        if ($isPro) {
            Subscription::create([
                'user_id' => $user->id,
                'plan' => 'premium',
                'billing_cycle' => 'monthly',
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'payment_method' => 'stripe',
                'amount_paid' => 19.99,
            ]);
        }

        return $user;
    }

    public function test_free_user_sees_upgrade_teaser_on_visitors_page(): void
    {
        $groom = $this->createSeeker('male', false);
        $bride = $this->createSeeker('female', false);

        // Record a view
        ProfileView::create([
            'viewer_id' => $bride->id,
            'viewed_id' => $groom->id,
            'viewed_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($groom)->get(route('profile.visitors'));

        $response->assertOk()
            ->assertSee('Who Viewed My Profile')
            ->assertSee('Seeker Premium Feature')
            ->assertSee('Upgrade to Seeker Premium');
    }

    public function test_pro_user_sees_actual_visitors_list(): void
    {
        $groom = $this->createSeeker('male', true);
        $bride = $this->createSeeker('female', false);

        ProfileView::create([
            'viewer_id' => $bride->id,
            'viewed_id' => $groom->id,
            'viewed_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($groom)->get(route('profile.visitors'));

        $response->assertOk()
            ->assertSee('Who Viewed My Profile')
            ->assertSee($bride->name)
            ->assertSee('Recent Suitor Visits')
            ->assertDontSee('Upgrade to Seeker Premium');
    }

    public function test_incognito_toggle_for_pro_and_free_users(): void
    {
        $freeUser = $this->createSeeker('male', false);
        $proUser = $this->createSeeker('male', true);

        // Free user cannot toggle incognito
        $response = $this->actingAs($freeUser)->post(route('profile.incognito.toggle'));
        $response->assertRedirect(route('subscription.pricing'));
        $this->assertFalse($freeUser->fresh()->is_incognito);

        // Pro user can toggle incognito ON
        $response = $this->actingAs($proUser)->post(route('profile.incognito.toggle'));
        $response->assertSessionHas('success');
        $this->assertTrue($proUser->fresh()->is_incognito);

        // Pro user can toggle incognito OFF
        $this->actingAs($proUser)->post(route('profile.incognito.toggle'));
        $this->assertFalse($proUser->fresh()->is_incognito);
    }

    public function test_incognito_pro_user_views_do_not_create_profile_views(): void
    {
        $proViewer = $this->createSeeker('male', true);
        $proViewer->update(['is_incognito' => true]);

        $targetBride = $this->createSeeker('female', false);

        $quotaService = app(QuotaService::class);
        $quotaService->recordView($proViewer, $targetBride->id);

        $this->assertDatabaseMissing('profile_views', [
            'viewer_id' => $proViewer->id,
            'viewed_id' => $targetBride->id,
        ]);
    }

    public function test_pro_user_can_shortlist_and_manage_notes(): void
    {
        $freeUser = $this->createSeeker('male', false);
        $proUser = $this->createSeeker('male', true);
        $candidate = $this->createSeeker('female', false);

        // Free user cannot bookmark
        $response = $this->actingAs($freeUser)->post(route('bookmarks.toggle', $candidate->id));
        $response->assertRedirect(route('subscription.pricing'));
        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $freeUser->id,
            'bookmarked_user_id' => $candidate->id,
        ]);

        // Pro user can bookmark
        $response = $this->actingAs($proUser)->post(route('bookmarks.toggle', $candidate->id));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $proUser->id,
            'bookmarked_user_id' => $candidate->id,
        ]);

        $bookmark = Bookmark::where('user_id', $proUser->id)->where('bookmarked_user_id', $candidate->id)->first();

        // Pro user can update family notes
        $response = $this->actingAs($proUser)->post(route('bookmarks.notes', $bookmark->id), [
            'notes' => 'Discuss prayer consistency with parents.',
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals('Discuss prayer consistency with parents.', $bookmark->fresh()->notes);

        // Toggling again un-shortlists the candidate
        $response = $this->actingAs($proUser)->post(route('bookmarks.toggle', $candidate->id));
        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $proUser->id,
            'bookmarked_user_id' => $candidate->id,
        ]);
    }

    public function test_printable_shariah_biodata_access(): void
    {
        $freeUser = $this->createSeeker('male', false);
        $proUser = $this->createSeeker('male', true);
        $candidate = $this->createSeeker('female', false);

        // User can always view their own biodata
        $response = $this->actingAs($freeUser)->get(route('profile.biodata'));
        $response->assertOk()
            ->assertSee('Shariah Matrimonial Biodata')
            ->assertSee($freeUser->name);

        // Pro user can view candidate's printable biodata
        $response = $this->actingAs($proUser)->get(route('profile.biodata.show', $candidate->id));
        $response->assertOk()
            ->assertSee('Shariah Matrimonial Biodata')
            ->assertSee($candidate->name);

        // Free user without accepted interest is redirected to pricing
        $response = $this->actingAs($freeUser)->get(route('profile.biodata.show', $candidate->id));
        $response->assertRedirect(route('subscription.pricing'));
    }
}
