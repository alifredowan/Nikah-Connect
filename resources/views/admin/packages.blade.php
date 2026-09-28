@extends('layouts.app')

@section('title', 'Manage Subscription Packages - Admin')

@section('content')
<div class="py-10 bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors" x-data="{
    displayFilter: 'all',
    showCreateModal: false,
    showEditModal: false,
    editPackage: {
        id: null,
        name: '',
        slug: '',
        description: '',
        monthly_price: 0,
        annual_price: 0,
        daily_profile_views: 10,
        daily_interests: 5,
        advanced_filters: false,
        profile_boost: false,
        see_who_viewed: false,
        dedicated_advisor: false,
        badge_text: '',
        features_text: '',
        is_active: true,
        is_popular: false,
        sort_order: 0
    },
    openEdit(pkg) {
        this.editPackage = {
            id: pkg.id,
            name: pkg.name,
            slug: pkg.slug,
            description: pkg.description || '',
            monthly_price: pkg.monthly_price,
            annual_price: pkg.annual_price,
            daily_profile_views: pkg.daily_profile_views,
            daily_interests: pkg.daily_interests,
            advanced_filters: !!pkg.advanced_filters,
            profile_boost: !!pkg.profile_boost,
            see_who_viewed: !!pkg.see_who_viewed,
            dedicated_advisor: !!pkg.dedicated_advisor,
            badge_text: pkg.badge_text || '',
            features_text: Array.isArray(pkg.features) ? pkg.features.join('\n') : '',
            is_active: !!pkg.is_active,
            is_popular: !!pkg.is_popular,
            sort_order: pkg.sort_order || 0
        };
        this.showEditModal = true;
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Top Breadcrumbs & Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 transition">&larr; Back to Admin Dashboard</a>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white font-heading mt-1">Dynamic Subscription Packages</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Setup, configure, and price subscription tiers with granular quotas and feature flags.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.promo-codes.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    🏷️ Promo Codes
                </a>
                <button @click="showCreateModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-900/20 transition flex items-center gap-1.5 cursor-pointer">
                    <span>+</span> Create New Package
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center gap-2">
                <span>✓</span> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-semibold flex items-center gap-2">
                <span>⚠️</span> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
                <span class="font-bold">Please correct the following errors:</span>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Package Display Selector Bar -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-bold shrink-0">
                    👁️
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">User Display Status</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Select which packages are displayed to seekers on the public pricing page. Currently <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $packages->where('is_active', true)->count() }} visible</span> to users.
                    </p>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl text-xs font-bold shrink-0">
                <button type="button" @click="displayFilter = 'all'"
                        :class="displayFilter === 'all' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-xl transition cursor-pointer">
                    All ({{ $packages->count() }})
                </button>
                <button type="button" @click="displayFilter = 'active'"
                        :class="displayFilter === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 dark:text-emerald-400 hover:text-emerald-800'"
                        class="px-3.5 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1">
                    🟢 Displayed ({{ $packages->where('is_active', true)->count() }})
                </button>
                <button type="button" @click="displayFilter = 'inactive'"
                        :class="displayFilter === 'inactive' ? 'bg-slate-700 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1">
                    🚫 Hidden ({{ $packages->where('is_active', false)->count() }})
                </button>
            </div>
        </div>

        <!-- Packages Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($packages as $pkg)
                <div x-show="displayFilter === 'all' || (displayFilter === 'active' && {{ $pkg->is_active ? 'true' : 'false' }}) || (displayFilter === 'inactive' && {{ ! $pkg->is_active ? 'true' : 'false' }})"
                     class="bg-white dark:bg-slate-900 rounded-3xl border {{ $pkg->is_popular ? 'border-2 border-emerald-500 ring-2 ring-emerald-100 dark:ring-emerald-950' : 'border-slate-200 dark:border-slate-800' }} p-6 shadow-xs flex flex-col justify-between relative transition">
                    <div>
                        <!-- Header Badges -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                @if($pkg->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Displayed to Users
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-500 uppercase tracking-wider">
                                        🚫 Hidden (Draft)
                                    </span>
                                @endif

                                @if($pkg->badge_text)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 uppercase tracking-wider">{{ $pkg->badge_text }}</span>
                                @endif
                            </div>
                            <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500">Slug: {{ $pkg->slug }}</span>
                        </div>

                        <!-- Title and Pricing -->
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white font-heading">{{ $pkg->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 min-h-[32px] line-clamp-2">{{ $pkg->description ?? 'No description provided.' }}</p>

                        <div class="my-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                            @if($pkg->isFree())
                                <div class="text-3xl font-extrabold text-slate-900 dark:text-white font-heading">$0.00</div>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Free forever tier</span>
                            @else
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-extrabold text-slate-900 dark:text-white font-heading">${{ number_format($pkg->monthly_price, 2) }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">/ month</span>
                                </div>
                                <div class="text-[11px] text-emerald-700 dark:text-emerald-400 font-bold mt-0.5">
                                    Annual: ${{ number_format($pkg->annual_price, 2) }} / yr
                                </div>
                            @endif
                        </div>

                        <!-- Quotas & Feature Matrix -->
                        <div class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Daily Profile Views:</span>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ $pkg->isUnlimitedViews() ? 'Unlimited (∞)' : $pkg->daily_profile_views . ' views' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Daily Interests:</span>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ $pkg->isUnlimitedInterests() ? 'Unlimited (∞)' : $pkg->daily_interests . ' requests' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Advanced Filters:</span>
                                <span>{!! $pkg->advanced_filters ? '<span class="text-emerald-600 font-bold">✓ Enabled</span>' : '<span class="text-slate-400">✕ None</span>' !!}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Profile Boost in Search:</span>
                                <span>{!! $pkg->profile_boost ? '<span class="text-amber-600 font-bold">⚡ Boosted</span>' : '<span class="text-slate-400">✕ Standard</span>' !!}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">See Who Viewed You:</span>
                                <span>{!! $pkg->see_who_viewed ? '<span class="text-emerald-600 font-bold">✓ Enabled</span>' : '<span class="text-slate-400">✕ None</span>' !!}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Dedicated Advisor:</span>
                                <span>{!! $pkg->dedicated_advisor ? '<span class="text-purple-600 font-bold">✓ VIP Advisor</span>' : '<span class="text-slate-400">✕ None</span>' !!}</span>
                            </div>
                        </div>

                        <!-- Custom Features Bullet List -->
                        @if(!empty($pkg->features) && count($pkg->features) > 0)
                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Displayed Highlights</span>
                                <ul class="space-y-1 text-[11px] text-slate-500 dark:text-slate-400">
                                    @foreach($pkg->features as $feat)
                                        <li class="flex items-start gap-1.5">
                                            <span class="text-emerald-600 dark:text-emerald-400">✓</span>
                                            <span>{{ $feat }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Actions Bar -->
                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                        <button @click="openEdit({{ $pkg->toJson() }})" class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition cursor-pointer">
                            ✏️ Edit
                        </button>

                        <div class="flex items-center gap-1.5">
                            <form action="{{ route('admin.packages.toggle-status', $pkg->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $pkg->is_active ? 'bg-amber-50 dark:bg-amber-950/60 hover:bg-amber-100 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs' }}" title="{{ $pkg->is_active ? 'Click to hide this package from users on pricing page' : 'Click to display this package to users on pricing page' }}">
                                    {{ $pkg->is_active ? '🚫 Hide from Users' : '👁️ Display to Users' }}
                                </button>
                            </form>

                            @if($pkg->slug !== 'free')
                                <form action="{{ route('admin.packages.destroy', $pkg->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete package {{ $pkg->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/60 text-rose-600 dark:text-rose-400 transition cursor-pointer" title="Delete Package">
                                        🗑️
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <!-- Create Package Modal -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showCreateModal = false" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading">Create Dynamic Subscription Package</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Configure package parameters, prices, quotas, and customer highlights.</p>
                </div>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('admin.packages.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Package Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Pro Seeker" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Slug (Identifier)</label>
                        <input type="text" name="slug" placeholder="e.g. pro (leave empty to auto-slug)" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Short Description</label>
                    <textarea name="description" rows="2" placeholder="Brief summary of this package tier..." class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Monthly Price ($) *</label>
                        <input type="number" step="0.01" min="0" name="monthly_price" required value="24.99" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Annual Price ($) *</label>
                        <input type="number" step="0.01" min="0" name="annual_price" required value="199.99" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Daily Profile Views Limit *</label>
                        <input type="number" name="daily_profile_views" required value="-1" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400 block mt-0.5">Use -1 for Unlimited views</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Daily Interest Requests Limit *</label>
                        <input type="number" name="daily_interests" required value="-1" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400 block mt-0.5">Use -1 for Unlimited interests</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Badge Text (Optional)</label>
                        <input type="text" name="badge_text" placeholder="e.g. Recommended, Popular" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="10" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Feature Toggles -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
                    <span class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Feature Flags</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="advanced_filters" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">Advanced Religious Filters</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="profile_boost" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">Profile Boost in Search (VIP)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="see_who_viewed" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">See Who Viewed Profile</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="dedicated_advisor" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">Dedicated Marriage Advisor</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_popular" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">Highlight as Most Popular</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer col-span-1 sm:col-span-2 p-2.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/60">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-900 dark:text-white font-bold text-xs">Display to Users on Public Pricing & Landing Page</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Custom Bullet Points (1 feature per line)</label>
                    <textarea name="features" rows="4" placeholder="Unlimited daily profile views
Unlimited interest requests
Priority ID verification queue
Halal Chaperoned Chat Support" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-900/20 transition">
                        Create Package
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Package Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showEditModal = false" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading">Edit Subscription Package</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Modify package pricing, limits, and feature toggles.</p>
                </div>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form :action="'{{ url('/admin/packages') }}/' + editPackage.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Package Name *</label>
                        <input type="text" name="name" x-model="editPackage.name" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Slug (Identifier) *</label>
                        <input type="text" name="slug" x-model="editPackage.slug" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Short Description</label>
                    <textarea name="description" x-model="editPackage.description" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Monthly Price ($) *</label>
                        <input type="number" step="0.01" min="0" name="monthly_price" x-model="editPackage.monthly_price" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Annual Price ($) *</label>
                        <input type="number" step="0.01" min="0" name="annual_price" x-model="editPackage.annual_price" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Daily Profile Views Limit *</label>
                        <input type="number" name="daily_profile_views" x-model="editPackage.daily_profile_views" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400 block mt-0.5">Use -1 for Unlimited views</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Daily Interest Requests Limit *</label>
                        <input type="number" name="daily_interests" x-model="editPackage.daily_interests" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400 block mt-0.5">Use -1 for Unlimited interests</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Badge Text</label>
                        <input type="text" name="badge_text" x-model="editPackage.badge_text" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Sort Order</label>
                        <input type="number" name="sort_order" x-model="editPackage.sort_order" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Feature Toggles -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
                    <span class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Feature Flags</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="advanced_filters" value="1" x-model="editPackage.advanced_filters" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">Advanced Religious Filters</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="profile_boost" value="1" x-model="editPackage.profile_boost" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">Profile Boost in Search (VIP)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="see_who_viewed" value="1" x-model="editPackage.see_who_viewed" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">See Who Viewed Profile</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="dedicated_advisor" value="1" x-model="editPackage.dedicated_advisor" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">Dedicated Marriage Advisor</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_popular" value="1" x-model="editPackage.is_popular" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">Highlight as Most Popular</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer col-span-1 sm:col-span-2 p-2.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/60">
                            <input type="checkbox" name="is_active" value="1" x-model="editPackage.is_active" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-900 dark:text-white font-bold text-xs">Display to Users on Public Pricing & Landing Page</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Custom Bullet Points (1 feature per line)</label>
                    <textarea name="features" x-model="editPackage.features_text" rows="4" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-900/20 transition">
                        Update Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
