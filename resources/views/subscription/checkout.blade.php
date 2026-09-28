@extends('layouts.app')

@section('title', 'Checkout - ' . $planDetails['name'])

@section('content')
<div class="py-12 bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors">
    <div class="max-w-xl mx-auto px-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-8"
             x-data="{
                cycle: 'monthly',
                monthlyPrice: {{ (float) $planDetails['monthly_price'] }},
                annualPrice: {{ (float) $planDetails['annual_price'] }},
                promoCode: '{{ old('promo_code', '') }}',
                promoApplied: false,
                promoLoading: false,
                discountPercentage: 0,
                promoMessage: '',
                promoError: '{{ $errors->first('promo_code') }}',
                currentBasePrice() {
                    return this.cycle === 'annual' ? this.annualPrice : this.monthlyPrice;
                },
                currentDiscount() {
                    if (!this.promoApplied) return 0;
                    return (this.currentBasePrice() * this.discountPercentage) / 100;
                },
                currentFinalPrice() {
                    return Math.max(0, this.currentBasePrice() - this.currentDiscount());
                },
                async checkPromo() {
                    if (!this.promoCode.trim()) {
                        this.promoError = 'Please enter a coupon or promo code.';
                        return;
                    }
                    this.promoLoading = true;
                    this.promoError = '';
                    this.promoMessage = '';
                    try {
                        let res = await fetch('{{ route('subscription.validate-promo') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                promo_code: this.promoCode,
                                plan: '{{ $plan }}',
                                billing_cycle: this.cycle
                            })
                        });
                        let data = await res.json();
                        if (res.ok && data.valid) {
                            this.promoApplied = true;
                            this.discountPercentage = data.discount_percentage;
                            this.promoMessage = data.message;
                        } else {
                            this.promoApplied = false;
                            this.promoError = data.message || 'Invalid promo code.';
                        }
                    } catch (e) {
                        this.promoError = 'Network error verifying promo code.';
                    } finally {
                        this.promoLoading = false;
                    }
                }
             }">
            <div class="text-center mb-6">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                    Upgrade to {{ $planDetails['name'] }}
                </span>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white font-heading mt-2">Complete Membership Upgrade</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Unlock unlimited discovery and enhanced matching features.</p>
            </div>

            @if($errors->any() && !$errors->has('promo_code'))
                <div class="mb-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs">
                    @foreach($errors->all() as $err)
                        <p>{{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('subscription.process') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="plan" value="{{ $plan }}">

                <!-- Billing Cycle Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Select Billing Cycle</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label @click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'border-2 border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/40' : 'border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800'" class="p-4 rounded-2xl cursor-pointer transition block text-left">
                            <input type="radio" name="billing_cycle" value="monthly" x-model="cycle" class="sr-only">
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Monthly Billing</span>
                            <span class="block text-lg font-extrabold text-slate-900 dark:text-white mt-1">${{ number_format($planDetails['monthly_price'], 2) }} <span class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">/ mo</span></span>
                        </label>

                        <label @click="cycle = 'annual'" :class="cycle === 'annual' ? 'border-2 border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/40' : 'border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800'" class="p-4 rounded-2xl cursor-pointer transition block text-left relative">
                            @if($planDetails['annual_price'] > 0 && $planDetails['monthly_price'] > 0)
                                @php
                                    $annualSavings = max(0, round((1 - ($planDetails['annual_price'] / ($planDetails['monthly_price'] * 12))) * 100));
                                @endphp
                                @if($annualSavings > 0)
                                    <span class="absolute -top-2.5 right-3 bg-amber-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase">Save {{ $annualSavings }}%</span>
                                @endif
                            @endif
                            <input type="radio" name="billing_cycle" value="annual" x-model="cycle" class="sr-only">
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Annual Billing</span>
                            <span class="block text-lg font-extrabold text-slate-900 dark:text-white mt-1">${{ number_format($planDetails['annual_price'], 2) }} <span class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">/ yr</span></span>
                        </label>
                    </div>
                </div>

                <!-- Promo Code Box (FR-3.5 with Special User Extra Discount Support) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Promotional Discount Code</label>
                    <div class="flex gap-2">
                        <input type="text" name="promo_code" x-model="promoCode" placeholder="e.g. PRO50, SPECIAL30"
                               @keydown.enter.prevent="checkPromo()"
                               class="flex-1 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none uppercase font-mono font-bold">
                        <button type="button" @click="checkPromo()" :disabled="promoLoading"
                                class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-black dark:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-bold transition cursor-pointer disabled:opacity-50">
                            <span x-show="!promoLoading">Apply</span>
                            <span x-show="promoLoading">Checking...</span>
                        </button>
                    </div>

                    <!-- Live feedback messages -->
                    <div x-show="promoMessage" x-cloak class="mt-2 p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-1.5">
                        <span>✓</span>
                        <span x-text="promoMessage"></span>
                    </div>

                    <div x-show="promoError" x-cloak class="mt-2 p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-semibold flex items-center gap-1.5">
                        <span>⚠️</span>
                        <span x-text="promoError"></span>
                    </div>

                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-1">Special VIP discount codes can be entered here to avail exclusive discounts on eligible packages.</span>
                </div>

                <!-- Live Order Summary -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span>Base Subscription (<span x-text="cycle === 'annual' ? 'Annual' : 'Monthly'"></span>):</span>
                        <span class="font-bold text-slate-900 dark:text-white" :class="promoApplied ? 'line-through text-slate-400 dark:text-slate-500' : ''">
                            $<span x-text="currentBasePrice().toFixed(2)"></span>
                        </span>
                    </div>

                    <div x-show="promoApplied" x-cloak class="flex items-center justify-between text-emerald-700 dark:text-emerald-400 font-bold">
                        <span>Extra Promo Discount (<span x-text="discountPercentage"></span>%):</span>
                        <span>-$<span x-text="currentDiscount().toFixed(2)"></span></span>
                    </div>

                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-sm font-extrabold text-slate-900 dark:text-white">
                        <span>Total Due Today:</span>
                        <span class="text-base text-emerald-700 dark:text-emerald-400">$<span x-text="currentFinalPrice().toFixed(2)"></span></span>
                    </div>
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

                <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-900/20 transition cursor-pointer">
                    Confirm & Activate Membership
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
