@extends('layouts.app')

@section('title', 'Platform Settings - Admin')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-700">&larr; Back to Admin Dashboard</a>
            <h1 class="text-2xl font-extrabold text-slate-900 font-heading mt-1">Platform Policy Parameters (FR-7.3)</h1>
            <p class="text-xs text-slate-500">Configure global moderation rules, subscription quotas, and promotional discounts without redeploying code.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Platform Policy Parameters Form -->
            <div class="md:col-span-2 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
                <h2 class="text-base font-bold text-slate-900 font-heading mb-4">Core Platform Policies</h2>
                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
                    @csrf

                    @foreach($settings as $setting)
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                {{ $setting->key }}
                            </label>
                            @if($setting->type === 'boolean')
                                <select name="{{ $setting->key }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                                    <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Enabled (True)</option>
                                    <option value="0" {{ $setting->value == '0' ? 'selected' : '' }}>Disabled (False)</option>
                                </select>
                            @else
                                <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                       class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                            @endif
                            <span class="text-[10px] text-slate-400 block mt-0.5">{{ $setting->description }}</span>
                        </div>
                    @endforeach

                    <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition mt-4">
                        Save Policy Parameters
                    </button>
                </form>
            </div>

            <!-- Promotional Codes Section -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h2 class="text-base font-bold text-slate-900 font-heading">Generate Promo Code</h2>
                    <form action="{{ route('admin.discounts.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Coupon Code</label>
                            <input type="text" name="code" required placeholder="e.g. BARAKAH30"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs uppercase font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discount %</label>
                            <input type="number" name="discount_percentage" required min="1" max="100" placeholder="e.g. 30"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Max Redemptions</label>
                            <input type="number" name="max_uses" value="100" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition">
                            Create Promo Code
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Active Promo Codes</h3>
                    <div class="space-y-2">
                        @foreach($discountCodes as $code)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs flex items-center justify-between">
                                <span class="font-mono font-bold text-emerald-800">{{ $code->code }}</span>
                                <span class="text-slate-600 font-semibold">{{ $code->discount_percentage }}% off ({{ $code->times_used }}/{{ $code->max_uses }} used)</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
