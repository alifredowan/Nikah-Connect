<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Services\Payment\StripePaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handleStripe(
        Request $request,
        StripePaymentService $stripeService,
        SubscriptionService $subscriptionService
    ): JsonResponse {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        // If webhook secret configured, verify signature
        if (config('services.stripe.webhook_secret')) {
            if (! $stripeService->verifyWebhookSignature($payload, $sigHeader)) {
                Log::warning('Stripe webhook signature verification failed.');

                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        $event = json_decode($payload, true);
        if (! is_array($event) || ! isset($event['type'])) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $eventType = $event['type'];
        Log::info("Received Stripe webhook event: {$eventType}");

        if ($eventType === 'checkout.session.completed') {
            $session = $event['data']['object'] ?? [];
            $sessionId = $session['id'] ?? null;
            $paymentStatus = $session['payment_status'] ?? null;

            if ($paymentStatus === 'paid' && $sessionId) {
                // Idempotency check
                $alreadyExists = Subscription::where('payment_id', $sessionId)->exists();
                if ($alreadyExists) {
                    return response()->json(['status' => 'already_processed']);
                }

                $metadata = $session['metadata'] ?? [];
                $userId = $metadata['user_id'] ?? null;
                $plan = $metadata['plan'] ?? null;
                $cycle = $metadata['billing_cycle'] ?? 'monthly';
                $promo = $metadata['promo_code'] ?: null;
                $amountTotal = isset($session['amount_total']) ? ((float) $session['amount_total']) / 100 : 0.0;

                $user = $userId ? User::find($userId) : null;
                if ($user && $plan) {
                    $subscriptionService->activateSubscription(
                        $user,
                        $plan,
                        $cycle,
                        $amountTotal,
                        'stripe',
                        $sessionId,
                        $promo,
                        $session
                    );

                    Log::info("Subscription activated via Stripe webhook for User {$user->id}, Plan {$plan}");
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function handlePayPal(
        Request $request,
        SubscriptionService $subscriptionService
    ): JsonResponse {
        $event = $request->all();
        $eventType = $event['event_type'] ?? null;

        Log::info("Received PayPal webhook event: {$eventType}");

        if (in_array($eventType, ['PAYMENT.CAPTURE.COMPLETED', 'CHECKOUT.ORDER.APPROVED'], true)) {
            $resource = $event['resource'] ?? [];
            $paymentId = $resource['id'] ?? null;

            if ($paymentId) {
                // Check if already processed
                $alreadyExists = Subscription::where('payment_id', $paymentId)->exists();
                if ($alreadyExists) {
                    return response()->json(['status' => 'already_processed']);
                }

                // Custom metadata might be in custom_id
                $customId = $resource['custom_id'] ?? null;
                $meta = [];
                if ($customId && str_starts_with($customId, '{')) {
                    $meta = json_decode($customId, true) ?: [];
                }

                $userId = $meta['user_id'] ?? null;
                $plan = $meta['plan'] ?? null;
                $cycle = $meta['billing_cycle'] ?? 'monthly';
                $promo = $meta['promo_code'] ?? null;
                $amount = (float) ($resource['amount']['value'] ?? 0.0);

                $user = $userId ? User::find($userId) : null;
                if ($user && $plan) {
                    $subscriptionService->activateSubscription(
                        $user,
                        $plan,
                        $cycle,
                        $amount,
                        'paypal',
                        $paymentId,
                        $promo,
                        $resource
                    );

                    Log::info("Subscription activated via PayPal webhook for User {$user->id}, Plan {$plan}");
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
