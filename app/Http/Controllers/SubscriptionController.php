<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DiscountCode;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
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

    public function checkout(string $plan): View|RedirectResponse
    {
        $planModel = SubscriptionPlan::where('slug', $plan)
            ->where('is_active', true)
            ->first();

        if (! $planModel || $planModel->isFree()) {
            // Also allow fallback if plan is 'premium' or 'premium_plus'
            if (in_array($plan, ['premium', 'premium_plus'], true)) {
                $planDetails = Subscription::getPlanDetails($plan);

                return view('subscription.checkout', compact('plan', 'planDetails'));
            }

            return redirect()->route('subscription.pricing')->with('error', 'Invalid or inactive plan selected for checkout.');
        }

        $planDetails = $planModel->toLegacyPlanDetails();

        return view('subscription.checkout', compact('plan', 'planModel', 'planDetails'));
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

    public function process(Request $request): RedirectResponse
    {
        $request->validate([
            'plan' => ['required', 'string'],
            'billing_cycle' => ['required', 'in:monthly,annual'],
            'promo_code' => ['nullable', 'string'],
        ]);

        $user = Auth::user();

        $planModel = SubscriptionPlan::where('slug', $request->plan)
            ->where('is_active', true)
            ->first();

        if (! $planModel && ! in_array($request->plan, ['premium', 'premium_plus'], true)) {
            return redirect()->route('subscription.pricing')->with('error', 'Selected membership package does not exist or is inactive.');
        }

        $planDetails = $planModel ? $planModel->toLegacyPlanDetails() : Subscription::getPlanDetails($request->plan);

        $basePrice = $request->billing_cycle === 'annual'
            ? (float) $planDetails['annual_price']
            : (float) $planDetails['monthly_price'];

        $finalPrice = $basePrice;
        $appliedPromo = null;

        // Check promo discount code
        if ($request->filled('promo_code')) {
            $discount = DiscountCode::where('code', strtoupper(trim($request->promo_code)))->first();

            if (! $discount) {
                return back()->withErrors(['promo_code' => 'The entered promo code does not exist.'])->withInput();
            }

            $validity = $discount->checkValidity($user, $request->plan);
            if (! $validity['valid']) {
                return back()->withErrors(['promo_code' => $validity['reason']])->withInput();
            }

            $discountAmount = round(($basePrice * $discount->discount_percentage) / 100, 2);
            $finalPrice = max(0, round($basePrice - $discountAmount, 2));
            $discount->increment('times_used');
            $appliedPromo = $discount;
        }

        // Cancel existing active subscriptions
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled', 'ends_at' => now()]);

        // Create new active subscription
        $periodMonths = $request->billing_cycle === 'annual' ? 12 : 1;

        $newSub = Subscription::create([
            'user_id' => $user->id,
            'plan' => $request->plan,
            'billing_cycle' => $request->billing_cycle,
            'status' => 'active',
            'amount_paid' => $finalPrice,
            'payment_method' => 'card_simulated',
            'starts_at' => now(),
            'ends_at' => now()->addMonths($periodMonths),
            'renews_at' => now()->addMonths($periodMonths),
        ]);

        $logData = [
            'plan' => $request->plan,
            'amount' => $finalPrice,
        ];

        if ($appliedPromo) {
            $logData['promo_code'] = $appliedPromo->code;
            $logData['discount_percentage'] = $appliedPromo->discount_percentage;
        }

        AuditLog::record($user->id, 'subscription_upgraded', 'Subscription', $newSub->id, $logData);

        $successMessage = "Congratulations! You have successfully upgraded to {$planDetails['name']}!";
        if ($appliedPromo) {
            $successMessage .= " (Promo code {$appliedPromo->code} applied: {$appliedPromo->discount_percentage}% off)";
        }

        return redirect()->route('subscription.pricing')->with('success', $successMessage);
    }

    public function cancel(): RedirectResponse
    {
        $user = Auth::user();

        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update([
                'status' => 'cancelled',
                'ends_at' => now(),
            ]);

        // Downgrade to free
        Subscription::create([
            'user_id' => $user->id,
            'plan' => 'free',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        AuditLog::record($user->id, 'subscription_cancelled', 'User', $user->id);

        return redirect()->route('subscription.pricing')->with('info', 'Your subscription has been cancelled and downgraded to the Free tier (FR-5.4).');
    }
}
