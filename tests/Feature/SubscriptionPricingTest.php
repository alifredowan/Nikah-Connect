<?php

namespace Tests\Feature;

use Tests\TestCase;

class SubscriptionPricingTest extends TestCase
{
    public function test_guests_can_view_pricing_and_register_without_an_active_plan(): void
    {
        $response = $this->get(route('subscription.pricing'));

        $response->assertSee('Membership Plans & Tiers', false)
            ->assertSee('Free Seeker')
            ->assertSee('Seeker Premium')
            ->assertSee('Seeker VIP (Premium+)')
            ->assertSee('Get Started for Free')
            ->assertSee('href="'.route('register').'"', false)
            ->assertDontSee('Current Active Plan')
            ->assertDontSee('Your Current Plan')
            ->assertDontSee('Downgrade to Free');
    }
}
