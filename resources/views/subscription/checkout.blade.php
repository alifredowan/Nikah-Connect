@extends('layouts.app')

@section('title', 'Checkout - ' . $planDetails['name'])

@section('content')
<div class="py-12 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-xl mx-auto px-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-8">
            <div class="text-center mb-6">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                    Upgrade to {{ $planDetails['name'] }}
                </span>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white font-heading mt-2">Complete Membership Upgrade</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Unlock unlimited discovery and enhanced matching features.</p>
            </div>

            <form action="{{ route('subscription.process') }}" method="POST" class="space-y-6" x-data="{ cycle: 'monthly', monthlyPrice: {{ $planDetails['monthly_price'] }}, annualPrice: {{ $planDetails['annual_price'] }} }">
                @csrf
                <input type="hidden" name="plan" value="{{ $plan }}">

                <!-- Billing Cycle Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Select Billing Cycle</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label @click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'border-2 border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/40' : 'border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800'" class="p-4 rounded-2xl cursor-pointer transition block text-left">
                            <input type="radio" name="billing_cycle" value="monthly" x-model="cycle" class="sr-only">
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Monthly Billing</span>
                            <span class="block text-lg font-extrabold text-slate-900 dark:text-white mt-1">${{ $planDetails['monthly_price'] }} <span class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">/ mo</span></span>
                        </label>

                        <label @click="cycle = 'annual'" :class="cycle === 'annual' ? 'border-2 border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/40' : 'border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800'" class="p-4 rounded-2xl cursor-pointer transition block text-left relative">
                            <span class="absolute -top-2.5 right-3 bg-amber-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase">Save 33%</span>
                            <input type="radio" name="billing_cycle" value="annual" x-model="cycle" class="sr-only">
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Annual Billing</span>
                            <span class="block text-lg font-extrabold text-slate-900 dark:text-white mt-1">${{ $planDetails['annual_price'] }} <span class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">/ yr</span></span>
                        </label>
                    </div>
                </div>

                <!-- Promo Code Box (FR-3.5) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Promotional Discount Code</label>
                    <div class="flex gap-2">
                        <input type="text" name="promo_code" value="HALAL20" placeholder="e.g. HALAL20, BARAKAH50"
                               class="flex-1 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none uppercase font-mono">
                    </div>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-1">Try demo promo code: <strong class="text-emerald-700 dark:text-emerald-400">HALAL20</strong> (20% off) or <strong class="text-emerald-700 dark:text-emerald-400">BARAKAH50</strong> (50% off).</span>
                </div>

                <!-- Simulated PCI-DSS Payment Gateway -->
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs space-y-3">
                    <div class="flex items-center justify-between text-slate-700 dark:text-slate-300 font-bold">
                        <span>Payment Processing</span>
                        <span class="text-slate-400 dark:text-slate-500 text-[10px]">PCI-DSS Compliant (FR-5.2)</span>
                    </div>
                    <div class="p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl flex items-center gap-2">
                        <span>💳</span>
                        <span class="text-slate-500 dark:text-slate-400 font-mono text-xs">•••• •••• •••• 4242</span>
                        <span class="ml-auto text-[10px] text-emerald-700 dark:text-emerald-400 font-bold">Simulated Test Mode</span>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-900/20 transition">
                    Confirm & Activate Membership
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
