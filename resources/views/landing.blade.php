@extends('layouts.app')

@section('title', 'Nikah Connect - Blessed Halal Muslim Matrimonial Platform')

@section('content')
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-emerald-950 text-white islamic-pattern pt-12 pb-24 lg:pt-20 lg:pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Left Column Text -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/80 border border-emerald-700/60 text-xs font-semibold text-emerald-200 backdrop-blur-xs">
                        <span class="text-amber-400">★</span> Pure Intentions, Halal Unions
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.15] text-white">
                        Find Your Righteous Partner with <span class="text-amber-400">Dignity</span> and <span class="text-emerald-300">Family Barakah</span>.
                    </h1>
                    <p class="text-lg sm:text-xl text-emerald-100/90 leading-relaxed font-light max-w-2xl">
                        Nikah Connect is an ethical matchmaking platform built strictly for marriage (Nikah), not casual dating. Featuring verified identities, guardian (Wali) participation, default photo blurring, and deen-weighted compatibility.
                    </p>
                    <div class="flex flex-wrap items-center gap-4 pt-4">
                        <a href="{{ route('register') }}" class="px-7 py-4 rounded-xl font-bold text-base text-emerald-950 bg-amber-400 hover:bg-amber-300 shadow-xl shadow-amber-900/30 transition transform hover:-translate-y-0.5">
                            Create Halal Profile
                        </a>
                        <a href="{{ route('discovery.index') }}" class="px-7 py-4 rounded-xl font-semibold text-base text-white bg-emerald-800/80 hover:bg-emerald-700/80 border border-emerald-600/40 backdrop-blur-xs transition">
                            Browse Candidates
                        </a>
                    </div>
                    <!-- Stats Badges -->
                    <div class="grid grid-cols-3 gap-6 pt-8 border-t border-emerald-800/60">
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-amber-300 font-heading">{{ $stats['total_seekers'] }}+</div>
                            <div class="text-xs text-emerald-200/80">Active Seekers</div>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-white font-heading">{{ $stats['verified_profiles'] }}</div>
                            <div class="text-xs text-emerald-200/80">KYC Verified</div>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-300 font-heading">100%</div>
                            <div class="text-xs text-emerald-200/80">Halal Governed</div>
                        </div>
                    </div>
                </div>

                <!-- Right Column Showcase Card -->
                <div class="lg:col-span-5">
                    <div class="relative mx-auto max-w-md bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 shadow-2xl">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/10">
                            <span class="text-xs uppercase font-bold tracking-wider text-emerald-200">Islamic Matchmaking Standard</span>
                            <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/40">
                                🛡️ Halal Certified
                            </span>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3.5 p-3 rounded-xl bg-white/5 border border-white/10">
                                <span class="w-10 h-10 rounded-lg bg-emerald-800/80 flex items-center justify-center text-lg shrink-0">🛡️</span>
                                <div>
                                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Guardian (Wali) Involvement</h4>
                                    <p class="text-[11px] text-emerald-100/80 mt-0.5">Dignified chaperone participation to protect every introduction.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3.5 p-3 rounded-xl bg-white/5 border border-white/10">
                                <span class="w-10 h-10 rounded-lg bg-amber-800/60 flex items-center justify-center text-lg shrink-0">🔒</span>
                                <div>
                                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Photo Privacy by Default</h4>
                                    <p class="text-[11px] text-emerald-100/80 mt-0.5">Photos remain blurred until mutual interest is formally confirmed.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3.5 p-3 rounded-xl bg-white/5 border border-white/10">
                                <span class="w-10 h-10 rounded-lg bg-blue-800/60 flex items-center justify-center text-lg shrink-0">🪪</span>
                                <div>
                                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Verified Identity (KYC)</h4>
                                    <p class="text-[11px] text-emerald-100/80 mt-0.5">Human moderator review of official national ID & passport documents.</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-white/10">
                                <span class="text-emerald-300 font-semibold flex items-center gap-1.5">
                                    <span>✓</span> Zero Tolerance for Dating
                                </span>
                                <span class="text-amber-300 font-semibold">100% Strictly for Nikah</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Pillars of Islamic Matrimony -->
    <div class="py-20 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight sm:text-4xl">
                    Distinctly Islamic by Design, Not an Afterthought
                </h2>
                <p class="mt-4 text-base text-slate-600 dark:text-slate-400">
                    Unlike standard dating applications, Nikah Connect strictly adheres to Islamic marriage jurisprudence, family involvement, and modesty standards.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Pillar 1 -->
                <div class="bg-white dark:bg-slate-800/80 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-700/60 shadow-xs hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 flex items-center justify-center text-2xl mb-5">
                        🛡️
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Wali (Guardian) Model</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Female profiles can link a father or mahram with granular permissions: view-only, approval-required before chat unlocks, or full proxy.
                    </p>
                </div>

                <!-- Pillar 2 -->
                <div class="bg-white dark:bg-slate-800/80 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-700/60 shadow-xs hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 flex items-center justify-center text-2xl mb-5">
                        🔒
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Photo Privacy by Default</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Photos are blurred by default across search and discovery. Full unblurred photos unlock only upon mutual consent and reciprocal approval.
                    </p>
                </div>

                <!-- Pillar 3 -->
                <div class="bg-white dark:bg-slate-800/80 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-700/60 shadow-xs hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 flex items-center justify-center text-2xl mb-5">
                        ⚖️
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Deen-Weighted Algorithm</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Matches are ranked using a custom compatibility engine that weights prayer frequency, madhhab, and halal practice higher than superficial metrics.
                    </p>
                </div>

                <!-- Pillar 4 -->
                <div class="bg-white dark:bg-slate-800/80 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-700/60 shadow-xs hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300 flex items-center justify-center text-2xl mb-5">
                        💬
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Chaperoned Halal Chat</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        No unsolicited messaging. Communication unlocks only after mutual interest. All chats are protected by contact-sharing and anti-harassment scanners.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Candidate Profiles Showcase -->
    @if($recentProfiles->isNotEmpty())
        <div class="py-16 bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-start md:items-end justify-between mb-10 gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Verified Community</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Recent Candidate Profiles</h2>
                    </div>
                    <a href="{{ route('discovery.index') }}" class="text-sm font-semibold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 flex items-center gap-1">
                        View all candidates &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($recentProfiles as $profile)
                        <div class="bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs hover:shadow-lg transition group">
                            <div class="relative h-48 bg-slate-800 overflow-hidden">
                                @if($profile->primaryPhoto)
                                    <img src="{{ $profile->primaryPhoto->displayUrl() }}" alt="{{ $profile->user->name }}" class="w-full h-full object-cover photo-blur group-hover:scale-105 transition transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-3xl">
                                        {{ substr($profile->user->name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                <div class="absolute top-3 right-3">
                                    @if($profile->user->is_verified)
                                        <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                                            ✓ Verified
                                        </span>
                                    @endif
                                </div>
                                <div class="absolute bottom-3 left-3 right-3 text-white">
                                    <p class="text-xs text-amber-300 font-semibold">{{ $profile->sect_madhhab ?? 'Muslim' }}</p>
                                    <h4 class="font-bold text-base truncate">{{ $profile->user->name }}</h4>
                                    <p class="text-xs text-slate-200">{{ $profile->user->age }} yrs • {{ $profile->city }}, {{ $profile->country }}</p>
                                </div>
                            </div>
                            <div class="p-4 space-y-3">
                                <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                                    <span>🕌 {{ str_replace('_', ' ', $profile->prayer_frequency ?? 'Practicing') }}</span>
                                    <span>🎓 {{ Str::limit($profile->profession ?? 'Professional', 16) }}</span>
                                </div>
                                <a href="{{ route('discovery.show', $profile->user_id) }}" class="block text-center w-full py-2.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-emerald-600 dark:hover:bg-emerald-600 text-slate-700 dark:text-slate-200 hover:text-white dark:hover:text-white border border-slate-200 dark:border-slate-700 hover:border-emerald-600 text-xs font-bold transition">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Pricing Tiers Comparison Section (FR-5.1) -->
    <div class="py-20 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Fair & Transparent Membership</span>
                <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight sm:text-4xl mt-1">
                    Free for All Core Islamic Features
                </h2>
                <p class="mt-4 text-base text-slate-600 dark:text-slate-400">
                    Paying is never a precondition for halal interaction or wali supervision. Premium tiers simply provide unlimited search and visibility boosts.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Free Tier -->
                <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-8 border border-slate-200 dark:border-slate-700 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200">Free Tier</span>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-3 font-heading">Seeker Free</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Everything needed to find your spouse</p>
                        <div class="my-6">
                            <span class="text-4xl font-extrabold text-slate-900 dark:text-white font-heading">$0</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">/ forever</span>
                        </div>
                        <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-700 pt-6">
                            <li class="flex items-center gap-2">✓ Profile creation & basic search</li>
                            <li class="flex items-center gap-2">✓ 10 daily profile views</li>
                            <li class="flex items-center gap-2">✓ 5 daily interest requests</li>
                            <li class="flex items-center gap-2">✓ Wali link & supervision</li>
                            <li class="flex items-center gap-2">✓ Blurred photo privacy</li>
                            <li class="flex items-center gap-2">✓ Halal messaging upon mutual accept</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="mt-8 block text-center w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-800 dark:text-white text-sm font-bold transition">
                        Get Started Free
                    </a>
                </div>

                <!-- Premium Tier -->
                <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-8 border-2 border-emerald-600 dark:border-emerald-500 shadow-xl relative flex flex-col justify-between transform md:-translate-y-2">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full text-xs font-extrabold bg-emerald-600 text-white uppercase tracking-wider">
                        Most Popular
                    </div>
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">Premium</span>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-3 font-heading">Seeker Premium</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Full access with unlimited discovery</p>
                        <div class="my-6">
                            <span class="text-4xl font-extrabold text-slate-900 dark:text-white font-heading">$19.99</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">/ month</span>
                        </div>
                        <ul class="space-y-3 text-sm text-slate-700 dark:text-slate-300 border-t border-slate-100 dark:border-slate-700 pt-6">
                            <li class="flex items-center gap-2 font-semibold text-emerald-700 dark:text-emerald-400">✓ Unlimited daily profile views</li>
                            <li class="flex items-center gap-2 font-semibold text-emerald-700 dark:text-emerald-400">✓ Unlimited interest requests</li>
                            <li class="flex items-center gap-2">✓ Advanced filters (sect, madhhab, diet)</li>
                            <li class="flex items-center gap-2">✓ See who viewed your profile</li>
                            <li class="flex items-center gap-2">✓ Wali chaperone controls included</li>
                            <li class="flex items-center gap-2">✓ Priority verified badge review</li>
                        </ul>
                    </div>
                    <a href="{{ route('subscription.checkout', 'premium') }}" class="mt-8 block text-center w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-900/20 transition">
                        Upgrade to Premium
                    </a>
                </div>

                <!-- Premium+ Tier -->
                <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-8 border border-slate-200 dark:border-slate-700 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300">VIP Premium+</span>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-3 font-heading">Seeker VIP</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Profile boost & dedicated support</p>
                        <div class="my-6">
                            <span class="text-4xl font-extrabold text-slate-900 dark:text-white font-heading">$39.99</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">/ month</span>
                        </div>
                        <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-700 pt-6">
                            <li class="flex items-center gap-2 font-semibold text-amber-700 dark:text-amber-400">✓ Top profile boost in search</li>
                            <li class="flex items-center gap-2">✓ All Premium tier features included</li>
                            <li class="flex items-center gap-2">✓ Dedicated marriage advisor contact</li>
                            <li class="flex items-center gap-2">✓ VIP support queue</li>
                        </ul>
                    </div>
                    <a href="{{ route('subscription.checkout', 'premium_plus') }}" class="mt-8 block text-center w-full py-3 rounded-xl bg-slate-900 dark:bg-slate-700 hover:bg-black dark:hover:bg-slate-600 text-white text-sm font-bold transition">
                        Choose VIP
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
