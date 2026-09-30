@extends('layouts.app')

@section('title', 'Manage Promo Codes & Special Discounts - Admin')

@section('content')
<div class="py-10 bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors" x-data="{
    code: '',
    targetType: 'all',
    allowedEmails: '',
    generateCode() {
        const prefixes = ['PRO', 'VIP', 'NIKAH', 'HALAL', 'SPECIAL', 'BARAKAH'];
        const p = prefixes[Math.floor(Math.random() * prefixes.length)];
        const num = Math.floor(100 + Math.random() * 900);
        this.code = p + num;
    },
    addEmail(email) {
        if (!this.allowedEmails.includes(email)) {
            this.allowedEmails = this.allowedEmails ? this.allowedEmails + ', ' + email : email;
        }
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 transition">&larr; Back to Admin Dashboard</a>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white font-heading mt-1">Promo Codes & Special Discounts</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Generate promo codes, target specific subscription packages (e.g. Pro), and assign extra discounts to special VIP users.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.packages.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    💳 Subscription Packages
                </a>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Generate Promo Code Form -->
            <div class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">Generate New Promo Code</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Setup coupon codes with package restriction and special user access control.</p>
                </div>

                <form action="{{ route('admin.promo-codes.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Promo Code *</label>
                            <button type="button" @click="generateCode()" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer">
                                🎲 Random Code
                            </button>
                        </div>
                        <input type="text" name="code" x-model="code" required placeholder="e.g. PRO50, SPECIAL30"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 text-xs font-mono uppercase font-bold outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Extra Discount Percentage (%) *</label>
                        <div class="relative">
                            <input type="number" name="discount_percentage" required min="1" max="100" value="30" placeholder="e.g. 30"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold outline-none focus:ring-2 focus:ring-emerald-500">
                            <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">% off</span>
                        </div>
                    </div>

                    <!-- Target Package -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Applicable Package *</label>
                        <select name="plan_slug" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="all">All Paid Packages</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->slug }}">
                                    {{ $pkg->name }} (${{ number_format($pkg->monthly_price, 2) }}/mo)
                                </option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-0.5">Restrict discount to a specific package (e.g. Pro package) or allow on all.</span>
                    </div>

                    <!-- User Eligibility / Special Users -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
                        <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">User Eligibility</label>
                        <div class="space-y-2 text-xs">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="target_mode" value="all" x-model="targetType" class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-slate-700 dark:text-slate-300 font-medium">Public (Any registered user)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="target_mode" value="special" x-model="targetType" class="text-amber-600 focus:ring-amber-500">
                                <span class="text-amber-800 dark:text-amber-300 font-bold">Special Users Only (VIP Restricted)</span>
                            </label>
                        </div>

                        <div x-show="targetType === 'special'" x-cloak class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">Designated Special User Email(s):</label>
                            <textarea name="allowed_emails" x-model="allowedEmails" rows="3" placeholder="user1@example.com, seeker@domain.com"
                                      class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-mono outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                            <span class="text-[10px] text-slate-500 block">Separate multiple emails with commas or newlines. Only these users will be granted this discount.</span>

                            @if(isset($allUsers) && count($allUsers) > 0)
                                <div class="pt-1">
                                    <span class="text-[10px] font-bold text-slate-400 block mb-1">Click to add registered user:</span>
                                    <div class="max-h-24 overflow-y-auto space-y-1">
                                        @foreach($allUsers->take(8) as $usr)
                                            <button type="button" @click="addEmail('{{ $usr->email }}')" class="block w-full text-left px-2 py-1 rounded-lg text-[10px] bg-white dark:bg-slate-900 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-300 transition truncate">
                                                + {{ $usr->name }} ({{ $usr->email }})
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Max Redemptions</label>
                            <input type="number" name="max_uses" required value="100" min="1" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Expiry Date (Opt.)</label>
                            <input type="date" name="expires_at" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Internal Note / Description</label>
                        <input type="text" name="description" placeholder="e.g. VIP discount for special matrimonial seekers" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-900/20 transition cursor-pointer">
                        Generate & Save Promo Code
                    </button>
                </form>
            </div>

            <!-- Right: Active Promo Codes Table -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">Active & Configured Promo Codes</h2>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ count($discountCodes) }} Total Codes</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-500 uppercase text-[10px] tracking-wider">
                                    <th class="py-3 px-3">Code</th>
                                    <th class="py-3 px-3">Discount</th>
                                    <th class="py-3 px-3">Package Target</th>
                                    <th class="py-3 px-3">Audience</th>
                                    <th class="py-3 px-3">Usage</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($discountCodes as $code)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                        <!-- Code -->
                                        <td class="py-3.5 px-3">
                                            <div class="flex items-center gap-1.5 font-mono font-bold text-sm text-emerald-700 dark:text-emerald-400">
                                                <span>{{ $code->code }}</span>
                                            </div>
                                            @if($code->description)
                                                <span class="text-[10px] text-slate-400 block line-clamp-1">{{ $code->description }}</span>
                                            @endif
                                        </td>

                                        <!-- Discount -->
                                        <td class="py-3.5 px-3 font-extrabold text-slate-900 dark:text-white text-sm">
                                            {{ $code->discount_percentage }}%
                                        </td>

                                        <!-- Package Target -->
                                        <td class="py-3.5 px-3">
                                            @if($code->plan_slug && $code->plan_slug !== 'all')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">
                                                    {{ $code->plan?->name ?? strtoupper($code->plan_slug) }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                                    All Paid Packages
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Audience -->
                                        <td class="py-3.5 px-3">
                                            @if($code->isRestrictedToUsers())
                                                <div class="group relative inline-block">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 cursor-help">
                                                        ★ Special Users ({{ count($code->getAllowedEmailsList()) }})
                                                    </span>
                                                    <div class="hidden group-hover:block absolute z-20 left-0 bottom-full mb-1 p-2 bg-slate-900 text-white text-[10px] rounded-xl shadow-lg max-w-xs whitespace-normal font-mono">
                                                        {{ implode(', ', $code->getAllowedEmailsList()) }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">
                                                    Public (All)
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Usage -->
                                        <td class="py-3.5 px-3 font-medium">
                                            <span>{{ $code->times_used }} / {{ $code->max_uses }}</span>
                                            @if($code->expires_at)
                                                <span class="text-[10px] text-slate-400 block">Exp: {{ $code->expires_at->format('M d, Y') }}</span>
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td class="py-3.5 px-3">
                                            @if(! $code->is_active)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500">Inactive</span>
                                            @elseif($code->times_used >= $code->max_uses)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Exhausted</span>
                                            @elseif($code->expires_at && $code->expires_at->isPast())
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Expired</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">Active</span>
                                            @endif
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-3.5 px-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <form action="{{ route('admin.promo-codes.toggle-status', $code->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer {{ $code->is_active ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200' : 'bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' }}">
                                                        {{ $code->is_active ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.promo-codes.destroy', $code->id) }}" method="POST" onsubmit="return confirm('Delete promo code {{ $code->code }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950 text-rose-600 dark:text-rose-400 transition cursor-pointer" title="Delete">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                            No promo codes created yet. Use the form on the left to generate one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 text-xs text-amber-900 dark:text-amber-200 space-y-1">
                    <span class="font-bold flex items-center gap-1.5">💡 Special User Discounts Guide:</span>
                    <p class="text-[11px] leading-relaxed text-amber-800 dark:text-amber-300">
                        When creating a promo code targeted at <strong>Special Users</strong>, only logged-in seekers whose email addresses match the allowed list can redeem it. If another user attempts to apply the code, or attempts to apply it to a package other than the target package (e.g. Pro), the checkout engine securely blocks the discount and explains why.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
