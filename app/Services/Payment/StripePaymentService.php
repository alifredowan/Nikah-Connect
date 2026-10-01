<?php

namespace App\Services\Payment;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripePaymentService
{
    protected ?string $secretKey;

    protected ?string $publicKey;

    protected ?string $webhookSecret;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret');
        $this->publicKey = config('services.stripe.key');
        $this->webhookSecret = config('services.stripe.webhook_secret');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->secretKey);
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * Create a Stripe Checkout Session for subscription payment.
     *
     * @return array{success: bool, url?: string, id?: string, error?: string}
     */
    public function createCheckoutSession(
        User $user,
        string $planSlug,
        string $planName,
        float $amount,
        string $billingCycle,
        ?string $promoCode = null
    ): array {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Stripe credentials are not configured.',
            ];
        }

        $amountInCents = (int) round($amount * 100);

        try {
            $response = Http::asForm()
                ->withToken($this->secretKey)
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'mode' => 'payment',
                    'customer_email' => $user->email,
                    'client_reference_id' => (string) $user->id,
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => 'usd',
                                'unit_amount' => $amountInCents,
                                'product_data' => [
                                    'name' => "Nikah Connect: {$planName} ({$billingCycle})",
                                    'description' => "Membership subscription upgrade for {$user->name}",
                                ],
                            ],
                            'quantity' => 1,
                        ],
                    ],
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'plan' => $planSlug,
                        'billing_cycle' => $billingCycle,
                        'promo_code' => $promoCode ?? '',
                        'amount' => (string) $amount,
                    ],
                    'success_url' => route('subscription.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('subscription.checkout', ['plan' => $planSlug]).'?status=cancelled',
                ]);

            if ($response->successful()) {
                $session = $response->json();

                return [
                    'success' => true,
                    'url' => $session['url'] ?? null,
                    'id' => $session['id'] ?? null,
                ];
            }

            Log::error('Stripe Checkout Session creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('error.message') ?? 'Failed to initiate Stripe checkout session.',
            ];
        } catch (\Throwable $e) {
            Log::error('Stripe exception: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return [
                'success' => false,
                'error' => 'An error occurred while connecting to Stripe.',
            ];
        }
    }

    /**
     * Retrieve and verify a Stripe Checkout Session.
     *
     * @return array{success: bool, paid: bool, data?: array, error?: string}
     */
    public function verifyCheckoutSession(string $sessionId): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'paid' => false,
                'error' => 'Stripe credentials are not configured.',
            ];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

            if ($response->successful()) {
                $session = $response->json();
                $isPaid = ($session['payment_status'] ?? '') === 'paid';

                return [
                    'success' => true,
                    'paid' => $isPaid,
                    'data' => $session,
                ];
            }

            return [
                'success' => false,
                'paid' => false,
                'error' => $response->json('error.message') ?? 'Invalid session.',
            ];
        } catch (\Throwable $e) {
            Log::error('Stripe verify session error: '.$e->getMessage());

            return [
                'success' => false,
                'paid' => false,
                'error' => 'Failed to verify Stripe checkout session.',
            ];
        }
    }

    /**
     * Verify Stripe webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signatureHeader): bool
    {
        if (empty($this->webhookSecret) || empty($signatureHeader)) {
            return false;
        }

        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $signatureHeader) as $part) {
            $item = explode('=', trim($part), 2);
            if (count($item) === 2) {
                if ($item[0] === 't') {
                    $timestamp = $item[1];
                } elseif ($item[0] === 'v1') {
                    $signatures[] = $item[1];
                }
            }
        }

        if (! $timestamp || empty($signatures)) {
            return false;
        }

        // Check timestamp tolerance (300 seconds)
        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $signedPayload = "{$timestamp}.{$payload}";
        $expectedSignature = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                return true;
            }
        }

        return false;
    }
}
