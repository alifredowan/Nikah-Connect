@extends('layouts.app')

@section('title', 'Membership Plans & Pricing - Nikah Connect')

@section('content')
<div class="py-12 bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <div class="text-center max-w-2xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Ethical & Transparent</span>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white font-heading mt-1">Membership Plans & Tiers</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                Core Islamic matching features, guardian supervision, and halal messaging are always accessible on the Free tier. Premium plans unlock unlimited discovery and profile visibility boosts (FR-5.1).
            </p>
        </div>

        @if(session('success'))
            <div class="max-w-2xl mx-auto p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center gap-2">
                <span>✓</span> {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="max-w-2xl mx-auto p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 text-xs font-semibold flex items-center gap-2">
                <span>ℹ️</span> {{ session('info') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="max-w-2xl mx-auto p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs font-semibold flex items-center gap-2">
                <span>⚠️</span> {{ session('warning') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(4, max(3, count($plans))) }} gap-8 items-stretch">
            @foreach($plans as $plan)
                @php
                    $isCurrent = ($currentPlan === $plan->slug);
                    $isFree = $plan->isFree();
                    $isPopular = (bool) $plan->is_popular;
                @endphp

                <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border-2 {{ $isCurrent ? 'border-emerald-600 ring-4 ring-emerald-100 dark:ring-emerald-950/60' : ($isPopular ? 'border-emerald-500 shadow-xl' : 'border-slate-200 dark:border-slate-800 shadow-xs') }} relative flex flex-col justify-between transition">
                    @if($plan->badge_text)
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3.5 py-0.5 rounded-full text-[11px] font-extrabold {{ $isPopular ? 'bg-emerald-600 text-white shadow-xs' : 'bg-amber-500 text-white shadow-xs' }} uppercase tracking-wider">
                            {{ $plan->badge_text }}
                        </div>
                    @endif

                    <div>
                        <!-- Header badge -->
                        <div class="mb-2">
                            @if($isCurrent)
                                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">Your Current Plan</span>
                            @elseif($isFree)
                                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 uppercase tracking-wider">Free Tier</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 uppercase tracking-wider">Upgrade Tier</span>
                            @endif
                        </div>

                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-2 font-heading">{{ $plan->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 min-h-[32px]">{{ $plan->description }}</p>

                        <!-- Price -->
                        <div class="my-5">
                            @if($isFree)
                                <span class="text-4xl font-extrabold text-slate-900 dark:text-white font-heading">$0</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">/ forever</span>
                            @else
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-extrabold text-slate-900 dark:text-white font-heading">${{ number_format($plan->monthly_price, 2) }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">/ month</span>
                                </div>
                                @if($plan->annual_price > 0)
                                    <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-bold block mt-1">
                                        or ${{ number_format($plan->annual_price, 2) }} billed annually
                                    </span>
                                @endif
                            @endif
                        </div>

                        <!-- Feature list -->
                        <ul class="space-y-3 text-xs text-slate-700 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 pt-5">
                            @if(!empty($plan->features) && count($plan->features) > 0)
                                @foreach($plan->features as $featureItem)
                                    <li class="flex items-start gap-2">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">✓</span>
                                        <span>{{ $featureItem }}</span>
                                    </li>
                                @endforeach
                            @else
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>{{ $plan->isUnlimitedViews() ? 'Unlimited daily profile views' : $plan->daily_profile_views . ' daily profile views' }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>{{ $plan->isUnlimitedInterests() ? 'Unlimited interest requests' : $plan->daily_interests . ' daily interest requests' }}</span>
                                </li>
                                @if($plan->advanced_filters)
                                    <li class="flex items-center gap-2">
                                        <span class="text-emerald-600 font-bold">✓</span>
                                        <span>Advanced filters (sect, madhhab, diet)</span>
                                    </li>
                                @endif
                                @if($plan->profile_boost)
                                    <li class="flex items-center gap-2 font-bold text-amber-700 dark:text-amber-400">
                                        <span>⚡</span>
                                        <span>Top profile boost in search discovery</span>
                                    </li>
                                @endif
                                @if($plan->see_who_viewed)
                                    <li class="flex items-center gap-2">
                                        <span class="text-emerald-600 font-bold">✓</span>
                                        <span>See who viewed your profile</span>
                                    </li>
                                @endif
                                @if($plan->dedicated_advisor)
                                    <li class="flex items-center gap-2 font-bold text-purple-700 dark:text-purple-400">
                                        <span>★</span>
                                        <span>Dedicated marriage advisor contact</span>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </div>

                    <!-- CTA Action Button -->
                    <div class="mt-8">
                        @if($isCurrent)
                            <button disabled class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 text-xs font-bold cursor-not-allowed">
                                Current Active Plan
                            </button>
                        @elseif($isFree)
                            @if(Auth::guest())
                                <a href="{{ route('register') }}" class="block text-center w-full py-3 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition">
                                    Get Started for Free
                                </a>
                            @else
                                <form action="{{ route('subscription.cancel') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-3 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition cursor-pointer">
                                        Downgrade to Free
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('subscription.checkout', $plan->slug) }}" class="block text-center w-full py-3 rounded-xl {{ $isPopular ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'bg-slate-900 dark:bg-slate-800 hover:bg-black dark:hover:bg-slate-700 text-white' }} text-xs font-bold transition">
                                Upgrade to {{ $plan->name }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
