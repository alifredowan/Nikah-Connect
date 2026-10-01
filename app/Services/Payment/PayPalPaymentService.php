<?php

namespace App\Services\Payment;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalPaymentService
{
    protected ?string $clientId;

    protected ?string $clientSecret;

    protected string $mode;

    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->mode = config('services.paypal.mode', 'sandbox');
        $this->baseUrl = $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Get OAuth 2.0 access token from PayPal.
     */
    public function getAccessToken(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        return Cache::remember('paypal_access_token_'.md5($this->clientId), 3000, function (): ?string {
            try {
                $response = Http::asForm()
                    ->withBasicAuth($this->clientId, $this->clientSecret)
                    ->post("{$this->baseUrl}/v1/oauth2/token", [
                        'grant_type' => 'client_credentials',
                    ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::error('PayPal OAuth token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Throwable $e) {
                Log::error('PayPal OAuth exception: '.$e->getMessage());

                return null;
            }
        });
    }

    /**
     * Create a PayPal v2 Checkout Order.
     *
     * @return array{success: bool, url?: string, id?: string, error?: string}
     */
    public function createOrder(
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
                'error' => 'PayPal credentials are not configured.',
            ];
        }

        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return [
                'success' => false,
                'error' => 'Unable to authenticate with PayPal.',
            ];
        }

        $customMetadata = json_encode([
            'user_id' => $user->id,
            'plan' => $planSlug,
            'billing_cycle' => $billingCycle,
            'promo_code' => $promoCode ?? '',
            'amount' => $amount,
        ]);

        try {
            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation',
                ])
                ->post("{$this->baseUrl}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'reference_id' => "sub_{$user->id}_".time(),
                            'description' => "Nikah Connect: {$planName} ({$billingCycle})",
                            'custom_id' => $customMetadata,
                            'amount' => [
                                'currency_code' => 'USD',
                                'value' => number_format($amount, 2, '.', ''),
                                'breakdown' => [
                                    'item_total' => [
                                        'currency_code' => 'USD',
                                        'value' => number_format($amount, 2, '.', ''),
                                    ],
                                ],
                            ],
                            'items' => [
                                [
                                    'name' => "{$planName} ({$billingCycle})",
                                    'unit_amount' => [
                                        'currency_code' => 'USD',
                                        'value' => number_format($amount, 2, '.', ''),
                                    ],
                                    'quantity' => '1',
                                    'category' => 'DIGITAL_GOODS',
                                ],
                            ],
                        ],
                    ],
                    'application_context' => [
                        'brand_name' => config('app.name', 'Nikah Connect'),
                        'locale' => 'en-US',
                        'landing_page' => 'NO_PREFERENCE',
                        'user_action' => 'PAY_NOW',
                        'return_url' => route('subscription.paypal.success'),
                        'cancel_url' => route('subscription.checkout', ['plan' => $planSlug]).'?status=cancelled',
                    ],
                ]);

            if ($response->successful()) {
                $order = $response->json();
                $approveUrl = null;

                foreach ($order['links'] ?? [] as $link) {
                    if (($link['rel'] ?? '') === 'approve') {
                        $approveUrl = $link['href'];
                        break;
                    }
                }

                if ($approveUrl) {
                    return [
                        'success' => true,
                        'url' => $approveUrl,
                        'id' => $order['id'] ?? null,
                    ];
                }

                return [
                    'success' => false,
                    'error' => 'PayPal approval link was not found in response.',
                ];
            }

            Log::error('PayPal Order creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Failed to initiate PayPal order.',
            ];
        } catch (\Throwable $e) {
            Log::error('PayPal Order exception: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return [
                'success' => false,
                'error' => 'An error occurred while connecting to PayPal.',
            ];
        }
    }

    /**
     * Capture payment for an approved PayPal Order.
     *
     * @return array{success: bool, order_id?: string, data?: array, error?: string}
     */
    public function captureOrder(string $orderId): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'PayPal credentials are not configured.',
            ];
        }

        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return [
                'success' => false,
                'error' => 'Unable to authenticate with PayPal.',
            ];
        }

        try {
            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

            if ($response->successful()) {
                $data = $response->json();
                $isCompleted = ($data['status'] ?? '') === 'COMPLETED';

                if ($isCompleted) {
                    return [
                        'success' => true,
                        'order_id' => $orderId,
                        'data' => $data,
                    ];
                }

                return [
                    'success' => false,
                    'error' => "PayPal order status is '{$data['status']}'. Payment was not completed.",
                    'data' => $data,
                ];
            }

            Log::error('PayPal Capture failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Failed to capture PayPal payment.',
            ];
        } catch (\Throwable $e) {
            Log::error('PayPal capture exception: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'An error occurred while capturing PayPal payment.',
            ];
        }
    }

    /**
     * Get order details from PayPal.
     *
     * @return array{success: bool, data?: array, error?: string}
     */
    public function getOrderDetails(string $orderId): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'PayPal credentials are not configured.',
            ];
        }

        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return [
                'success' => false,
                'error' => 'Unable to authenticate with PayPal.',
            ];
        }

        try {
            $response = Http::withToken($accessToken)
                ->get("{$this->baseUrl}/v2/checkout/orders/{$orderId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Failed to fetch PayPal order.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'An error occurred while fetching PayPal order.',
            ];
        }
    }
}
