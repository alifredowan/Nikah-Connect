<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentProcessTest extends TestCase
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

        SubscriptionPlan::updateOrCreate(
            ['slug' => 'premium'],
            [
                'name' => 'Seeker Premium',
                'monthly_price' => 19.99,
                'annual_price' => 159.99,
                'daily_profile_views' => 999999,
                'daily_interests' => 999999,
                'advanced_filters' => true,
                'see_who_viewed' => true,
                'is_active' => true,
            ]
        );
    }

    private function createSeeker(): User
    {
        return User::factory()->create([
            'email' => 'seeker.'.Str::random(8).'@example.com',
            'role' => 'seeker',
            'is_verified' => true,
            'is_active' => true,
        ]);
    }

    public function test_pricing_page_displays_stripe_and_paypal_trust_badges(): void
    {
        $response = $this->get(route('subscription.pricing'));

        $response->assertStatus(200)
            ->assertSee('Stripe Checkout')
            ->assertSee('PayPal Express');
    }

    public function test_checkout_page_renders_payment_options(): void
    {
        $seeker = $this->createSeeker();

        $response = $this->actingAs($seeker)->get(route('subscription.checkout', 'premium'));

        $response->assertStatus(200)
            ->assertSee('Credit / Debit Card')
            ->assertSee('Stripe')
            ->assertSee('PayPal')
            ->assertSee('Developer / Demo Mode');
    }

    public function test_checkout_process_with_simulated_payment(): void
    {
        $seeker = $this->createSeeker();

        $response = $this->actingAs($seeker)->post(route('subscription.process'), [
            'plan' => 'premium',
            'billing_cycle' => 'monthly',
            'payment_method' => 'simulated',
        ]);

        $response->assertRedirect(route('subscription.pricing'));

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $seeker->id,
            'plan' => 'premium',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'payment_method' => 'card_simulated',
            'amount_paid' => 19.99,
        ]);
    }

    public function test_stripe_checkout_redirection_when_configured(): void
    {
        config([
            'services.stripe.secret' => 'sk_test_dummy_secret_key',
            'services.stripe.key' => 'pk_test_dummy_public_key',
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_mock_session_123',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_mock_session_123',
            ], 200),
        ]);

        $seeker = $this->createSeeker();

        $response = $this->actingAs($seeker)->post(route('subscription.process'), [
            'plan' => 'premium',
            'billing_cycle' => 'monthly',
            'payment_method' => 'stripe',
        ]);

        $response->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_mock_session_123');
    }

    public function test_stripe_success_callback_verifies_session_and_activates_subscription(): void
    {
        config([
            'services.stripe.secret' => 'sk_test_dummy_secret_key',
        ]);

        $seeker = $this->createSeeker();

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_test_mock_session_123' => Http::response([
                'id' => 'cs_test_mock_session_123',
                'payment_status' => 'paid',
                'amount_total' => 1999,
                'metadata' => [
                    'user_id' => (string) $seeker->id,
                    'plan' => 'premium',
                    'billing_cycle' => 'monthly',
                    'promo_code' => '',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($seeker)->get(route('subscription.stripe.success', [
            'session_id' => 'cs_test_mock_session_123',
        ]));

        $response->assertRedirect(route('subscription.pricing'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $seeker->id,
            'plan' => 'premium',
            'payment_method' => 'stripe',
            'payment_id' => 'cs_test_mock_session_123',
            'status' => 'active',
            'amount_paid' => 19.99,
        ]);
    }

    public function test_paypal_checkout_redirection_when_configured(): void
    {
        config([
            'services.paypal.client_id' => 'dummy_paypal_client_id',
            'services.paypal.client_secret' => 'dummy_paypal_client_secret',
            'services.paypal.mode' => 'sandbox',
        ]);

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'mock_paypal_access_token_123',
                'expires_in' => 3600,
            ], 200),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAYPAL-ORDER-12345',
                'links' => [
                    [
                        'rel' => 'approve',
                        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-12345',
                    ],
                ],
            ], 200),
        ]);

        $seeker = $this->createSeeker();

        $response = $this->actingAs($seeker)->post(route('subscription.process'), [
            'plan' => 'premium',
            'billing_cycle' => 'monthly',
            'payment_method' => 'paypal',
        ]);

        $response->assertRedirect('https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-12345');
    }

    public function test_paypal_success_callback_captures_order_and_activates_subscription(): void
    {
        config([
            'services.paypal.client_id' => 'dummy_paypal_client_id',
            'services.paypal.client_secret' => 'dummy_paypal_client_secret',
            'services.paypal.mode' => 'sandbox',
        ]);

        $seeker = $this->createSeeker();

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'mock_paypal_access_token_123',
                'expires_in' => 3600,
            ], 200),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/PAYPAL-ORDER-12345/capture' => Http::response([
                'status' => 'COMPLETED',
                'purchase_units' => [
                    [
                        'custom_id' => json_encode([
                            'user_id' => $seeker->id,
                            'plan' => 'premium',
                            'billing_cycle' => 'monthly',
                            'promo_code' => '',
                        ]),
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => '19.99',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($seeker)->get(route('subscription.paypal.success', [
            'token' => 'PAYPAL-ORDER-12345',
            'PayerID' => 'PAYER987',
        ]));

        $response->assertRedirect(route('subscription.pricing'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $seeker->id,
            'plan' => 'premium',
            'payment_method' => 'paypal',
            'payment_id' => 'PAYPAL-ORDER-12345',
            'status' => 'active',
            'amount_paid' => 19.99,
        ]);
    }

    public function test_stripe_webhook_activates_subscription_idempotently(): void
    {
        $seeker = $this->createSeeker();

        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_webhook_test_999',
                    'payment_status' => 'paid',
                    'amount_total' => 1999,
                    'metadata' => [
                        'user_id' => (string) $seeker->id,
                        'plan' => 'premium',
                        'billing_cycle' => 'monthly',
                        'promo_code' => '',
                    ],
                ],
            ],
        ];

        // First webhook call
        $response = $this->postJson(route('webhook.stripe'), $payload);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $seeker->id,
            'plan' => 'premium',
            'payment_id' => 'cs_webhook_test_999',
            'payment_method' => 'stripe',
            'status' => 'active',
        ]);

        // Duplicate webhook call should be idempotent
        $duplicateResponse = $this->postJson(route('webhook.stripe'), $payload);
        $duplicateResponse->assertStatus(200)
            ->assertJson(['status' => 'already_processed']);

        $count = Subscription::where('payment_id', 'cs_webhook_test_999')->count();
        $this->assertEquals(1, $count);
    }

    public function test_paypal_webhook_activates_subscription_idempotently(): void
    {
        $seeker = $this->createSeeker();

        $payload = [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE-PAYPAL-777',
                'custom_id' => json_encode([
                    'user_id' => $seeker->id,
                    'plan' => 'premium',
                    'billing_cycle' => 'monthly',
                    'promo_code' => '',
                ]),
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => '19.99',
                ],
            ],
        ];

        // First webhook call
        $response = $this->postJson(route('webhook.paypal'), $payload);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $seeker->id,
            'plan' => 'premium',
            'payment_id' => 'CAPTURE-PAYPAL-777',
            'payment_method' => 'paypal',
            'status' => 'active',
        ]);

        // Duplicate webhook call
        $duplicateResponse = $this->postJson(route('webhook.paypal'), $payload);
        $duplicateResponse->assertStatus(200)
            ->assertJson(['status' => 'already_processed']);
    }

    public function test_cancelled_status_shows_notice_on_checkout(): void
    {
        $seeker = $this->createSeeker();

        $response = $this->actingAs($seeker)->get(route('subscription.checkout', [
            'plan' => 'premium',
            'status' => 'cancelled',
        ]));

        $response->assertStatus(200)
            ->assertSee('Checkout was cancelled');
    }
}
