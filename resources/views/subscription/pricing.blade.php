@extends('layouts.app')

@section('title', 'Membership Plans & Pricing - Nikah Connect')

@section('content')
<div class="py-12 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <div class="text-center max-w-2xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Ethical & Transparent</span>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white font-heading mt-1">Membership Plans & Tiers</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                Core Islamic matching features, guardian supervision, and halal messaging are always accessible on the Free tier. Premium plans unlock unlimited discovery and profile visibility boosts (FR-5.1).
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Free Tier -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border {{ $currentPlan === 'free' ? 'border-2 border-emerald-500 ring-2 ring-emerald-100 dark:ring-emerald-950' : 'border-slate-200 dark:border-slate-800' }} shadow-xs flex flex-col justify-between">
                <div>
                    @if($currentPlan === 'free')
                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 uppercase">Your Current Plan</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 uppercase">Free Tier</span>
                    @endif
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-3 font-heading">Free Seeker</h3>
                    <div class="my-5">
                        <span class="text-4xl font-extrabold text-slate-900 dark:text-white font-heading">$0</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">/ forever</span>
                    </div>
                    <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 pt-5">
                        <li class="flex items-center gap-2">✓ 10 daily profile views (FR-3.4)</li>
                        <li class="flex items-center gap-2">✓ 5 daily interest requests (FR-5.1)</li>
                        <li class="flex items-center gap-2">✓ Full Wali guardian supervision (FR-4.3)</li>
                        <li class="flex items-center gap-2">✓ Default blurred photo privacy (FR-2.2)</li>
                        <li class="flex items-center gap-2">✓ Halal messaging after mutual accept</li>
                    </ul>
                </div>
                <div class="mt-8">
                    @if($currentPlan === null)
                        <a href="{{ route('register') }}" class="block text-center w-full py-3 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition">
                            Get Started for Free
                        </a>
                    @elseif($currentPlan === 'free')
                        <button disabled class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 text-xs font-bold cursor-not-allowed">
                            Current Active Plan
                        </button>
                    @else
                        <form action="{{ route('subscription.cancel') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-3 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition">
                                Downgrade to Free
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Premium Tier -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border-2 {{ $currentPlan === 'premium' ? 'border-emerald-600 ring-4 ring-emerald-100 dark:ring-emerald-950' : 'border-emerald-500' }} shadow-xl relative flex flex-col justify-between">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-600 text-white uppercase tracking-wider">
                    Recommended
                </div>
                <div>
                    @if($currentPlan === 'premium')
                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 uppercase">Your Current Plan</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 uppercase">Most Popular</span>
                    @endif
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-3 font-heading">Seeker Premium</h3>
                    <div class="my-5">
                        <span class="text-4xl font-extrabold text-slate-900 dark:text-white font-heading">${{ $plans['premium']['monthly_price'] }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">/ month</span>
                    </div>
                    <ul class="space-y-3 text-xs text-slate-700 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 pt-5">
                        <li class="flex items-center gap-2 font-bold text-emerald-800 dark:text-emerald-400">✓ Unlimited daily profile views (FR-3.4)</li>
                        <li class="flex items-center gap-2 font-bold text-emerald-800 dark:text-emerald-400">✓ Unlimited interest requests (FR-5.1)</li>
                        <li class="flex items-center gap-2">✓ Advanced filters (sect, madhhab, diet)</li>
                        <li class="flex items-center gap-2">✓ See who viewed your profile</li>
                        <li class="flex items-center gap-2">✓ Priority ID verification queue</li>
                    </ul>
                </div>
                <div class="mt-8">
                    @if($currentPlan === 'premium')
                        <button disabled class="w-full py-3 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 text-xs font-bold cursor-not-allowed">
                            Current Active Plan
                        </button>
                    @else
                        <a href="{{ route('subscription.checkout', 'premium') }}" class="block text-center w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-900/20 transition">
                            Upgrade to Premium
                        </a>
                    @endif
                </div>
            </div>

            <!-- Premium+ Tier -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border {{ $currentPlan === 'premium_plus' ? 'border-2 border-amber-500 ring-2 ring-amber-100 dark:ring-amber-950' : 'border-slate-200 dark:border-slate-800' }} shadow-xs flex flex-col justify-between">
                <div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 uppercase">VIP Tier</span>
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-3 font-heading">Seeker VIP (Premium+)</h3>
                    <div class="my-5">
                        <span class="text-4xl font-extrabold text-slate-900 dark:text-white font-heading">${{ $plans['premium_plus']['monthly_price'] }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">/ month</span>
                    </div>
                    <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 pt-5">
                        <li class="flex items-center gap-2 font-bold text-amber-700 dark:text-amber-400">✓ Top profile boost in search (FR-5.1)</li>
                        <li class="flex items-center gap-2">✓ All Premium tier features included</li>
                        <li class="flex items-center gap-2">✓ Dedicated marriage advisor contact</li>
                        <li class="flex items-center gap-2">✓ VIP customer support</li>
                    </ul>
                </div>
                <div class="mt-8">
                    @if($currentPlan === 'premium_plus')
                        <button disabled class="w-full py-3 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 text-xs font-bold cursor-not-allowed">
                            Current Active Plan
                        </button>
                    @else
                        <a href="{{ route('subscription.checkout', 'premium_plus') }}" class="block text-center w-full py-3 rounded-xl bg-slate-900 dark:bg-slate-800 hover:bg-black dark:hover:bg-slate-700 text-white text-xs font-bold transition">
                            Choose VIP Premium+
                        </a>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
