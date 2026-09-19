@extends('layouts.app')

@section('title', 'Discover Halal Matches - Nikah Connect')

@section('content')
<div class="py-8 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Top Header & Usage Quota Indicator -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 font-heading">Halal Match Discovery</h1>
                <p class="text-xs text-slate-500 mt-1">Discover practicing Muslim candidates based on deen, values, and life goals.</p>
            </div>
            <!-- Quota Badge -->
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/80 px-4 py-2.5 rounded-xl text-xs">
                <div>
                    <span class="font-bold text-emerald-950">Daily Quota:</span>
                    @if($quotaStats['views_limit'] === 'Unlimited')
                        <span class="text-emerald-700 font-semibold ml-1">Unlimited Views & Interests ({{ ucfirst($quotaStats['plan']) }})</span>
                    @else
                        <span class="text-emerald-800 font-medium ml-1">
                            {{ $quotaStats['views_remaining'] }} / {{ $quotaStats['views_limit'] }} views left today
                        </span>
                    @endif
                </div>
                @if($quotaStats['plan'] === 'free')
                    <a href="{{ route('subscription.pricing') }}" class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shadow-xs transition">
                        Upgrade
                    </a>
                @endif
            </div>
        </div>

        <!-- Algorithmic Daily Matches Carousel/Row (FR-3.2) -->
        @if($dailyRecommendations->isNotEmpty())
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <h2 class="text-lg font-bold text-slate-900 font-heading">Ranked Daily Suggestions (Algorithmic Compatibility)</h2>
                    </div>
                    <span class="text-xs text-slate-500 font-medium">Deen-weighted compatibility matching (FR-6.4)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($dailyRecommendations as $match)
                        <div class="bg-white rounded-2xl border-2 border-emerald-100 p-5 shadow-xs hover:shadow-md transition relative flex flex-col justify-between">
                            <div class="absolute top-4 right-4 z-10">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    {{ $match->compatibility_score }}% Match
                                </span>
                            </div>
                            <div>
                                <div class="flex items-center gap-3.5 mb-4">
                                    <div class="relative w-14 h-14 rounded-xl overflow-hidden bg-slate-800 shrink-0">
                                        @if($match->primaryPhoto)
                                            <img src="{{ $match->primaryPhoto->displayUrl() }}" alt="{{ $match->user->name }}" class="w-full h-full object-cover photo-blur">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-emerald-900 text-white font-bold text-xl">
                                                {{ substr($match->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <span class="absolute inset-0 flex items-center justify-center bg-black/40 text-[9px] text-white font-bold">🔒 Blurred</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <h3 class="font-bold text-sm text-slate-900 truncate max-w-[140px]">{{ $match->user->name }}</h3>
                                            @if($match->user->is_verified)
                                                <svg class="w-3.5 h-3.5 text-blue-500 fill-current shrink-0" viewBox="0 0 20 20">
                                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500">{{ $match->user->age }} yrs • {{ $match->city }}, {{ $match->country }}</p>
                                        <p class="text-[11px] text-emerald-700 font-semibold">{{ $match->sect_madhhab }}</p>
                                    </div>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl text-xs text-slate-600 line-clamp-2 mb-4">
                                    {{ $match->bio ?? 'Committed to deen and building a righteous family.' }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                                <a href="{{ route('discovery.show', $match->user_id) }}" class="flex-1 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold text-center shadow-xs transition">
                                    View Full Profile
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Filter & Search Controls (FR-3.1) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs" x-data="{ advancedOpen: false }">
            <form action="{{ route('discovery.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Age Range</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="age_min" value="{{ request('age_min') }}" placeholder="Min (18)" min="18" max="80"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="text-slate-400 text-xs">-</span>
                            <input type="number" name="age_max" value="{{ request('age_max') }}" placeholder="Max (50)" min="18" max="80"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Country</label>
                        <input type="text" name="country" value="{{ request('country') }}" placeholder="e.g. United Kingdom, Italy, Canada"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Marital Status</label>
                        <select name="marital_status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                            <option value="">Any Status</option>
                            <option value="never_married" {{ request('marital_status') === 'never_married' ? 'selected' : '' }}>Never Married</option>
                            <option value="divorced" {{ request('marital_status') === 'divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="widowed" {{ request('marital_status') === 'widowed' ? 'selected' : '' }}>Widowed</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                            Apply Filters
                        </button>
                        <button type="button" @click="advancedOpen = !advancedOpen" class="py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                            ⚙️ More
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters Collapsible (FR-3.1, FR-5.1) -->
                <div x-show="advancedOpen" x-cloak class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Sect / Madhhab</label>
                        <select name="sect" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                            <option value="">Any Madhhab</option>
                            <option value="Sunni - Hanafi" {{ request('sect') === 'Sunni - Hanafi' ? 'selected' : '' }}>Sunni - Hanafi</option>
                            <option value="Sunni - Shafi'i" {{ request('sect') === 'Sunni - Shafi\'i' ? 'selected' : '' }}>Sunni - Shafi'i</option>
                            <option value="Sunni - Maliki" {{ request('sect') === 'Sunni - Maliki' ? 'selected' : '' }}>Sunni - Maliki</option>
                            <option value="Sunni - Hanbali" {{ request('sect') === 'Sunni - Hanbali' ? 'selected' : '' }}>Sunni - Hanbali</option>
                            <option value="Shia - Jafari" {{ request('sect') === 'Shia - Jafari' ? 'selected' : '' }}>Shia - Jafari</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Prayer Frequency</label>
                        <select name="prayer_frequency" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                            <option value="">Any</option>
                            <option value="5x_daily" {{ request('prayer_frequency') === '5x_daily' ? 'selected' : '' }}>Always 5x Daily</option>
                            <option value="occasional" {{ request('prayer_frequency') === 'occasional' ? 'selected' : '' }}>Regular / Occasional</option>
                        </select>
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="wali_required" value="1" {{ request('wali_required') ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span>🛡️ Guardian (Wali) Linked Only</span>
                        </label>
                    </div>
                </div>
            </form>
        </div>

        <!-- Search Results Grid -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 font-medium">Found {{ $profiles->total() }} matching candidate profiles</p>
                <!-- Save Search Modal Trigger -->
                <button type="button" onclick="document.getElementById('saveSearchModal').classList.remove('hidden')" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    💾 Save this Search (FR-3.3)
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($profiles as $p)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-lg transition flex flex-col justify-between">
                        <div>
                            <!-- Photo Container with Blurred Default (FR-2.2) -->
                            <div class="relative h-56 bg-slate-900 overflow-hidden">
                                @if($p->primaryPhoto)
                                    <img src="{{ $p->primaryPhoto->displayUrl() }}" alt="{{ $p->user->name }}" class="w-full h-full object-cover photo-blur">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-4xl">
                                        {{ substr($p->user->name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent"></div>

                                <!-- Badges -->
                                <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/50 text-white backdrop-blur-xs flex items-center gap-1">
                                        🔒 Blurred (FR-2.2)
                                    </span>
                                    @if($p->user->is_verified)
                                        <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-xs">
                                            ✓ Verified
                                        </span>
                                    @endif
                                </div>

                                <!-- Overlay text -->
                                <div class="absolute bottom-3 left-3 right-3 text-white">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-amber-300 font-semibold">{{ $p->sect_madhhab ?? 'Muslim' }}</span>
                                        @if(isset($p->compatibility_score))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500 text-white">
                                                {{ $p->compatibility_score }}% Match
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="font-bold text-base text-white truncate">{{ $p->user->name }}</h3>
                                    <p class="text-xs text-slate-300">{{ $p->user->age }} yrs • {{ $p->city }}, {{ $p->country }}</p>
                                </div>
                            </div>

                            <!-- Body Info -->
                            <div class="p-4 space-y-2.5 text-xs text-slate-600">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                    <span class="text-slate-500">Prayer:</span>
                                    <span class="font-semibold text-slate-800">{{ str_replace('_', ' ', $p->prayer_frequency ?? 'Practicing') }}</span>
                                </div>
                                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                    <span class="text-slate-500">Education:</span>
                                    <span class="font-semibold text-slate-800 truncate max-w-[140px]">{{ $p->education_level ?? 'Graduate' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Wali:</span>
                                    <span class="font-semibold {{ $p->wali_required ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ $p->wali_required ? '🛡️ Linked' : 'Self-managed' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 pt-0">
                            <a href="{{ route('discovery.show', $p->user_id) }}" class="block text-center w-full py-2.5 rounded-xl bg-slate-900 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                                View Profile
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200">
                        <p class="text-base text-slate-600 font-semibold">No candidates matched your filter criteria.</p>
                        <p class="text-xs text-slate-400 mt-1">Try widening your age or location filters.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="pt-4">
                {{ $profiles->links() }}
            </div>
        </div>

    </div>
</div>

<!-- Save Search Modal -->
<div id="saveSearchModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-slate-900 mb-2 font-heading">Save Search Criteria</h3>
        <p class="text-xs text-slate-500 mb-4">You will receive notifications whenever new candidates matching your current filters join the platform (FR-3.3).</p>
        <form action="{{ route('discovery.save-search') }}" method="POST">
            @csrf
            <input type="hidden" name="country" value="{{ request('country') }}">
            <input type="hidden" name="age_min" value="{{ request('age_min') }}">
            <input type="hidden" name="age_max" value="{{ request('age_max') }}">
            <input type="hidden" name="sect" value="{{ request('sect') }}">
            <input type="hidden" name="marital_status" value="{{ request('marital_status') }}">

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1">Search Title</label>
                <input type="text" name="title" required placeholder="e.g. Practicing Hanafi in UK"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('saveSearchModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700">Save Search</button>
            </div>
        </form>
    </div>
</div>
@endsection
