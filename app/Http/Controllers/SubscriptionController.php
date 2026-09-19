<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DiscountCode;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function pricing(): View
    {
        $user = Auth::user();
        $plans = [
            'free' => Subscription::getPlanDetails('free'),
            'premium' => Subscription::getPlanDetails('premium'),
            'premium_plus' => Subscription::getPlanDetails('premium_plus'),
        ];

        $currentPlan = $user->plan;

        return view('subscription.pricing', compact('plans', 'currentPlan'));
    }

    public function checkout(string $plan): View|RedirectResponse
    {
        if (! in_array($plan, ['premium', 'premium_plus'], true)) {
            return redirect()->route('subscription.pricing')->with('error', 'Invalid plan selected.');
        }

        $planDetails = Subscription::getPlanDetails($plan);

        return view('subscription.checkout', compact('plan', 'planDetails'));
    }

    public function process(Request $request): RedirectResponse
    {
        $request->validate([
            'plan' => ['required', 'in:premium,premium_plus'],
            'billing_cycle' => ['required', 'in:monthly,annual'],
            'promo_code' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $planDetails = Subscription::getPlanDetails($request->plan);

        $basePrice = $request->billing_cycle === 'annual'
            ? $planDetails['annual_price']
            : $planDetails['monthly_price'];

        $finalPrice = $basePrice;

        // Check promo discount code
        if ($request->filled('promo_code')) {
            $discount = DiscountCode::where('code', strtoupper($request->promo_code))->first();
            if ($discount && $discount->isValid()) {
                $discountAmount = ($basePrice * $discount->discount_percentage) / 100;
                $finalPrice = max(0, $basePrice - $discountAmount);
                $discount->increment('times_used');
            }
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

        AuditLog::record($user->id, 'subscription_upgraded', 'Subscription', $newSub->id, [
            'plan' => $request->plan,
            'amount' => $finalPrice,
        ]);

        return redirect()->route('subscription.pricing')->with('success', "Congratulations! You have successfully upgraded to {$planDetails['name']}! Enjoy unlimited profile views, advanced filters, and priority discovery.");
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
