@extends('layouts.app')

@section('title', 'Profile Visitors & Activity - Nikah Connect')

@section('content')
<div class="py-10 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Top Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Pro Feature</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Visitor Analytics</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-heading">Who Viewed My Profile</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Discover prospective suitors who viewed your matrimonial biodata.</p>
            </div>

            <!-- Incognito Privacy Controls -->
            <div class="w-full sm:w-auto bg-slate-50 dark:bg-slate-800/80 p-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-4">
                <div class="text-left">
                    <span class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>🕵️</span> Incognito Mode
                        @if($user->is_incognito)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">ACTIVE</span>
                        @endif
                    </span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $user->is_incognito ? 'Browsing privately without traces' : 'Your visits are visible to others' }}
                    </p>
                </div>

                @if($isPro)
                    <form action="{{ route('profile.incognito.toggle') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $user->is_incognito ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200 hover:bg-slate-300' }}">
                            {{ $user->is_incognito ? 'Turn Off' : 'Turn On' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('subscription.pricing') }}" class="px-3 py-1 rounded-xl text-[11px] font-bold bg-amber-500 text-white hover:bg-amber-600 transition">
                        PRO Only
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center gap-2">
                <span>✓</span> {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs font-semibold flex items-center gap-2">
                <span>⚠️</span> {{ session('warning') }}
            </div>
        @endif

        <!-- Quick Stats Overview Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl font-bold">
                    👁️
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Profile Views</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $totalViewsCount }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl font-bold">
                    👥
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Unique Suitors</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $uniqueVisitorsCount }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl font-bold">
                    ⭐
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Your Membership</span>
                    <span class="text-sm font-black text-slate-900 dark:text-white font-heading uppercase">{{ $user->plan }} Tier</span>
                </div>
            </div>
        </div>

        @if($isPro)
            <!-- Pro View: Real Visitors List -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white font-heading">Recent Suitor Visits</h2>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Real-time visitor logs</span>
                </div>

                @if($visitors->isEmpty())
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-12 text-center space-y-4">
                        <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-3xl">
                            🌱
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">No Profile Views Yet</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                            Ensure your Islamic biodata is 100% complete and verified to rank higher in daily compatibility matching!
                        </p>
                        <a href="{{ route('profile.edit') }}" class="inline-block px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                            Boost My Profile Details &rarr;
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($visitors as $view)
                            @php
                                $viewer = $view->viewer;
                                $profile = $viewer->profile;
                            @endphp
                            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2 mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="relative w-14 h-14 rounded-2xl overflow-hidden bg-slate-800 shrink-0">
                                                @if($profile && $profile->primaryPhoto)
                                                    <img src="{{ $profile->primaryPhoto->displayUrl() }}" alt="{{ $viewer->name }}"
                                                         class="w-full h-full object-cover {{ $view->is_photo_visible ? '' : 'photo-blur' }}">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-xl">
                                                        {{ substr($viewer->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                @if(!$view->is_photo_visible)
                                                    <span class="absolute inset-0 flex items-center justify-center bg-black/40 text-[9px] text-white font-bold">🔒 Blurred</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $viewer->name }}</h3>
                                                    @if($viewer->is_verified)
                                                        <svg class="w-3.5 h-3.5 text-blue-500 fill-current shrink-0" viewBox="0 0 20 20">
                                                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ $viewer->age }} yrs • {{ $profile?->city ?? 'Location not specified' }}
                                                </p>
                                                <p class="text-[11px] text-emerald-700 dark:text-emerald-400 font-semibold">
                                                    {{ $profile?->sect_madhhab ?? 'Muslim' }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($view->viewer_compatibility)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                                                {{ $view->viewer_compatibility }}% Match
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 py-2 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
                                        <span>🕒 Visited:</span>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $view->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/60 flex items-center gap-2">
                                    <a href="{{ route('discovery.show', $viewer->id) }}" class="flex-1 py-2 text-center rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition">
                                        View Profile
                                    </a>
                                    @if($view->existing_interest)
                                        <span class="px-3 py-2 rounded-xl text-xs font-bold text-center bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                            {{ ucfirst($view->existing_interest->status) }}
                                        </span>
                                    @else
                                        <form action="{{ route('interests.send', $viewer->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                                                <span>💌</span> Interest
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        @else
            <!-- Free Member Upgrade Teaser Card -->
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-950 via-slate-900 to-emerald-900 rounded-3xl p-8 sm:p-12 text-white shadow-xl border border-emerald-800/50">
                <div class="max-w-2xl space-y-5 relative z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/30 text-xs font-extrabold uppercase tracking-wider">
                        <span>🔒</span> Seeker Premium Feature
                    </div>

                    <h2 class="text-3xl sm:text-4xl font-extrabold font-heading leading-tight">
                        {{ $uniqueVisitorsCount }} Candidates Viewed Your Profile!
                    </h2>

                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Don't miss out on potential righteous life partners who have reviewed your matrimonial profile. Upgrade to Seeker Premium to unlock their identity, deen compatibility scores, and send instant halal interests!
                    </p>

                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <a href="{{ route('subscription.pricing') }}" class="px-7 py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs shadow-lg transition flex items-center justify-center gap-2 font-heading tracking-wide">
                            <span>⭐</span> Upgrade to Seeker Premium
                        </a>
                        <a href="{{ route('discovery.index') }}" class="px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs backdrop-blur-md transition text-center">
                            Continue Browsing
                        </a>
                    </div>
                </div>

                <!-- Teaser Blurred Silhouette Cards Preview -->
                <div class="mt-8 pt-8 border-t border-white/10 grid grid-cols-1 sm:grid-cols-3 gap-4 opacity-75">
                    @for($i = 1; $i <= min(3, max(1, $uniqueVisitorsCount)); $i++)
                        <div class="bg-white/5 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-xl shrink-0">
                                🔒
                            </div>
                            <div class="space-y-1">
                                <div class="h-3 w-24 bg-white/30 rounded-md filter blur-[2px]"></div>
                                <div class="h-2.5 w-32 bg-white/20 rounded-md filter blur-[2px]"></div>
                                <span class="text-[10px] text-amber-300 font-bold block">Recent Visitor #{{ $i }}</span>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
