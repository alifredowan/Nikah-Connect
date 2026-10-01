<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PayPalPaymentService;
use App\Services\Payment\StripePaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function pricing(): View
    {
        $user = Auth::user();
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('monthly_price')
            ->get();

        if ($plans->isEmpty()) {
            $plans = collect([
                new SubscriptionPlan(Subscription::getPlanDetails('free')),
                new SubscriptionPlan(Subscription::getPlanDetails('premium')),
                new SubscriptionPlan(Subscription::getPlanDetails('premium_plus')),
            ]);
        }

        $currentPlan = $user?->plan;

        return view('subscription.pricing', compact('plans', 'currentPlan'));
    }

    public function checkout(
        string $plan,
        StripePaymentService $stripeService,
        PayPalPaymentService $paypalService
    ): View|RedirectResponse {
        $planModel = SubscriptionPlan::where('slug', $plan)
            ->where('is_active', true)
            ->first();

        $stripeConfigured = $stripeService->isConfigured();
        $paypalConfigured = $paypalService->isConfigured();

        if (! $planModel || $planModel->isFree()) {
            // Also allow fallback if plan is 'premium' or 'premium_plus'
            if (in_array($plan, ['premium', 'premium_plus'], true)) {
                $planDetails = Subscription::getPlanDetails($plan);

                return view('subscription.checkout', compact('plan', 'planDetails', 'stripeConfigured', 'paypalConfigured'));
            }

            return redirect()->route('subscription.pricing')->with('error', 'Invalid or inactive plan selected for checkout.');
        }

        $planDetails = $planModel->toLegacyPlanDetails();

        return view('subscription.checkout', compact('plan', 'planModel', 'planDetails', 'stripeConfigured', 'paypalConfigured'));
    }

    public function validatePromo(Request $request): JsonResponse
    {
        $request->validate([
            'promo_code' => ['required', 'string'],
            'plan' => ['required', 'string'],
            'billing_cycle' => ['nullable', 'in:monthly,annual'],
        ]);

        $user = Auth::user();
        $plan = $request->input('plan');
        $cycle = $request->input('billing_cycle', 'monthly');

        $planModel = SubscriptionPlan::where('slug', $plan)->where('is_active', true)->first();
        $planDetails = $planModel ? $planModel->toLegacyPlanDetails() : Subscription::getPlanDetails($plan);

        $basePrice = $cycle === 'annual' ? (float) $planDetails['annual_price'] : (float) $planDetails['monthly_price'];

        $discount = DiscountCode::where('code', strtoupper(trim($request->promo_code)))->first();

        if (! $discount) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid promotional code.',
            ], 422);
        }

        $validity = $discount->checkValidity($user, $plan);

        if (! $validity['valid']) {
            return response()->json([
                'valid' => false,
                'message' => $validity['reason'],
            ], 422);
        }

        $discountPercentage = $discount->discount_percentage;
        $discountAmount = round(($basePrice * $discountPercentage) / 100, 2);
        $finalPrice = max(0, round($basePrice - $discountAmount, 2));

        return response()->json([
            'valid' => true,
            'code' => $discount->code,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'original_price' => $basePrice,
            'final_price' => $finalPrice,
            'message' => "Success! {$discountPercentage}% discount applied to {$planDetails['name']}.",
        ]);
    }

    public function process(
        Request $request,
        StripePaymentService $stripeService,
        PayPalPaymentService $paypalService,
        SubscriptionService $subscriptionService
    ): RedirectResponse {
        $request->validate([
            'plan' => ['required', 'string'],
            'billing_cycle' => ['required', 'in:monthly,annual'],
            'promo_code' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string', 'in:stripe,paypal,simulated'],
        ]);

        $user = Auth::user();

        $planModel = SubscriptionPlan::where('slug', $request->plan)
            ->where('is_active', true)
            ->first();

        if (! $planModel && ! in_array($request->plan, ['premium', 'premium_plus'], true)) {
            return redirect()->route('subscription.pricing')->with('error', 'Selected membership package does not exist or is inactive.');
        }

        $pricing = $subscriptionService->calculatePricing(
            $request->plan,
            $request->billing_cycle,
            $request->promo_code,
            $user
        );

        if ($request->filled('promo_code') && ! $pricing['applied_promo']) {
            return back()->withErrors(['promo_code' => 'The entered promo code is invalid or not applicable.'])->withInput();
        }

        $paymentMethod = $request->input('payment_method', 'simulated');
        $finalPrice = $pricing['final_price'];
        $planName = $pricing['plan_name'];

        // If price is 0 (e.g. 100% promo discount or free plan), instantly activate
        if ($finalPrice <= 0) {
            $subscriptionService->activateSubscription(
                $user,
                $request->plan,
                $request->billing_cycle,
                0.00,
                'free_promo',
                'promo_'.uniqid(),
                $pricing['applied_promo']?->code
            );

            return redirect()->route('subscription.pricing')->with('success', "Congratulations! {$planName} membership activated successfully!");
        }

        // Stripe Checkout Flow
        if ($paymentMethod === 'stripe') {
            if ($stripeService->isConfigured()) {
                $session = $stripeService->createCheckoutSession(
                    $user,
                    $request->plan,
                    $planName,
                    $finalPrice,
                    $request->billing_cycle,
                    $pricing['applied_promo']?->code
                );

                if ($session['success'] && ! empty($session['url'])) {
                    return redirect()->away($session['url']);
                }

                return back()->withErrors(['payment' => $session['error'] ?? 'Unable to connect to Stripe checkout.'])->withInput();
            }

            // Fallback for development without API keys
            if (app()->environment(['local', 'testing'])) {
                $subscriptionService->activateSubscription(
                    $user,
                    $request->plan,
                    $request->billing_cycle,
                    $finalPrice,
                    'stripe_simulated',
                    'stripe_sim_'.uniqid(),
                    $pricing['applied_promo']?->code
                );

                return redirect()->route('subscription.pricing')->with(
                    'success',
                    "Congratulations! Upgraded to {$planName} (Stripe Test/Dev Mode)!"
                );
            }

            return back()->withErrors(['payment' => 'Stripe is not currently configured.'])->withInput();
        }

        // PayPal Orders Flow
        if ($paymentMethod === 'paypal') {
            if ($paypalService->isConfigured()) {
                $order = $paypalService->createOrder(
                    $user,
                    $request->plan,
                    $planName,
                    $finalPrice,
                    $request->billing_cycle,
                    $pricing['applied_promo']?->code
                );

                if ($order['success'] && ! empty($order['url'])) {
                    return redirect()->away($order['url']);
                }

                return back()->withErrors(['payment' => $order['error'] ?? 'Unable to initiate PayPal order.'])->withInput();
            }

            // Fallback for development without API keys
            if (app()->environment(['local', 'testing'])) {
                $subscriptionService->activateSubscription(
                    $user,
                    $request->plan,
                    $request->billing_cycle,
                    $finalPrice,
                    'paypal_simulated',
                    'paypal_sim_'.uniqid(),
                    $pricing['applied_promo']?->code
                );

                return redirect()->route('subscription.pricing')->with(
                    'success',
                    "Congratulations! Upgraded to {$planName} (PayPal Test/Dev Mode)!"
                );
            }

            return back()->withErrors(['payment' => 'PayPal is not currently configured.'])->withInput();
        }

        // Default: Simulated Instant Card (maintains backward compatibility with tests)
        $newSub = $subscriptionService->activateSubscription(
            $user,
            $request->plan,
            $request->billing_cycle,
            $finalPrice,
            'card_simulated',
            'sim_'.uniqid(),
            $pricing['applied_promo']?->code
        );

        $successMessage = "Congratulations! You have successfully upgraded to {$planName}!";
        if ($pricing['applied_promo']) {
            $successMessage .= " (Promo code {$pricing['applied_promo']->code} applied: {$pricing['discount_percentage']}% off)";
        }

        return redirect()->route('subscription.pricing')->with('success', $successMessage);
    }

    public function stripeSuccess(
        Request $request,
        StripePaymentService $stripeService,
        SubscriptionService $subscriptionService
    ): RedirectResponse {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('subscription.pricing')->with('error', 'Stripe checkout session ID missing.');
        }

        $verification = $stripeService->verifyCheckoutSession($sessionId);

        if (! $verification['success'] || ! $verification['paid']) {
            return redirect()->route('subscription.pricing')->with('error', 'Payment could not be verified with Stripe. Please contact support if you were charged.');
        }

        $session = $verification['data'];
        $metadata = $session['metadata'] ?? [];
        $userId = $metadata['user_id'] ?? Auth::id();
        $plan = $metadata['plan'] ?? 'premium';
        $cycle = $metadata['billing_cycle'] ?? 'monthly';
        $promo = $metadata['promo_code'] ?: null;
        $amount = isset($session['amount_total']) ? ((float) $session['amount_total']) / 100 : 0.0;

        $user = Auth::user() ?: User::find($userId);

        if (! $user) {
            return redirect()->route('login')->with('warning', 'Please log in to review your active subscription.');
        }

        // Check if already processed by webhook
        $existing = Subscription::where('payment_id', $sessionId)->first();
        if (! $existing) {
            $subscriptionService->activateSubscription(
                $user,
                $plan,
                $cycle,
                $amount,
                'stripe',
                $sessionId,
                $promo,
                $session
            );
        }

        $planDetails = Subscription::getPlanDetails($plan);

        return redirect()->route('subscription.pricing')->with('success', "Payment successful! Welcome to {$planDetails['name']} via Stripe!");
    }

    public function paypalSuccess(
        Request $request,
        PayPalPaymentService $paypalService,
        SubscriptionService $subscriptionService
    ): RedirectResponse {
        $orderId = $request->query('token');

        if (! $orderId) {
            return redirect()->route('subscription.pricing')->with('error', 'PayPal transaction token missing.');
        }

        // Capture the order
        $capture = $paypalService->captureOrder($orderId);

        if (! $capture['success']) {
            // Check if already captured / completed
            $details = $paypalService->getOrderDetails($orderId);
            if (! $details['success'] || ($details['data']['status'] ?? '') !== 'COMPLETED') {
                return redirect()->route('subscription.pricing')->with('error', $capture['error'] ?? 'PayPal payment could not be completed.');
            }
            $captureData = $details['data'];
        } else {
            $captureData = $capture['data'];
        }

        // Check if already recorded
        $existing = Subscription::where('payment_id', $orderId)->first();
        $user = Auth::user();

        if (! $existing && $user) {
            // Extract purchase units custom_id
            $purchaseUnit = $captureData['purchase_units'][0] ?? [];
            $customId = $purchaseUnit['custom_id'] ?? null;
            $meta = [];
            if ($customId && str_starts_with($customId, '{')) {
                $meta = json_decode($customId, true) ?: [];
            }

            $plan = $meta['plan'] ?? 'premium';
            $cycle = $meta['billing_cycle'] ?? 'monthly';
            $promo = $meta['promo_code'] ?? null;
            $amount = (float) ($purchaseUnit['amount']['value'] ?? 0.0);

            $subscriptionService->activateSubscription(
                $user,
                $plan,
                $cycle,
                $amount,
                'paypal',
                $orderId,
                $promo,
                $captureData
            );
        }

        return redirect()->route('subscription.pricing')->with('success', 'Payment successful! Your membership has been activated via PayPal!');
    }

    public function cancel(SubscriptionService $subscriptionService): RedirectResponse
    {
        $user = Auth::user();
        $subscriptionService->cancelSubscription($user);

        return redirect()->route('subscription.pricing')->with('info', 'Your subscription has been cancelled and downgraded to the Free tier (FR-5.4).');
    }
}
