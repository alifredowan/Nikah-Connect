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
                paymentMethod: '{{ old('payment_method', 'stripe') }}',
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

            @if(request()->query('status') === 'cancelled')
                <div class="mb-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-center gap-2">
                    <span>ℹ️</span>
                    <span>Checkout was cancelled. You have not been charged. Choose your payment method below to complete whenever you're ready.</span>
                </div>
            @endif

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

                <!-- Payment Gateway Selector (Stripe & PayPal) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Select Payment Method
                    </label>

                    <input type="hidden" name="payment_method" :value="paymentMethod">

                    <div class="space-y-3">
                        <!-- Stripe Card Option -->
                        <div @click="paymentMethod = 'stripe'"
                             :class="paymentMethod === 'stripe'
                                ? 'border-2 border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/40 ring-2 ring-emerald-500/20'
                                : 'border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 hover:border-slate-300 dark:hover:border-slate-700'"
                             class="p-4 rounded-2xl cursor-pointer transition-all duration-200 relative group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black text-sm shadow-sm">
                                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-slate-900 dark:text-white">Credit / Debit Card</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">Stripe</span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Visa, Mastercard, Amex, Apple Pay & Google Pay</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition"
                                          :class="paymentMethod === 'stripe' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 dark:border-slate-600'">
                                        <span x-show="paymentMethod === 'stripe'" class="w-2 h-2 rounded-full bg-white"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                                <span>🔒 256-bit encrypted checkout</span>
                                <span class="font-mono text-[10px]">PCI-DSS Compliant</span>
                            </div>
                        </div>

                        <!-- PayPal Option -->
                        <div @click="paymentMethod = 'paypal'"
                             :class="paymentMethod === 'paypal'
                                ? 'border-2 border-sky-600 bg-sky-50/50 dark:bg-sky-950/40 ring-2 ring-sky-500/20'
                                : 'border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 hover:border-slate-300 dark:hover:border-slate-700'"
                             class="p-4 rounded-2xl cursor-pointer transition-all duration-200 relative group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#003087] text-white flex items-center justify-center font-black text-sm shadow-sm">
                                        <span class="font-sans italic font-extrabold text-lg tracking-tighter">P<span class="text-[#0079C1]">P</span></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-slate-900 dark:text-white">PayPal</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300">Buyer Protection</span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pay via PayPal balance, bank account, or PayPal credit</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition"
                                          :class="paymentMethod === 'paypal' ? 'border-sky-600 bg-sky-600 text-white' : 'border-slate-300 dark:border-slate-600'">
                                        <span x-show="paymentMethod === 'paypal'" class="w-2 h-2 rounded-full bg-white"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                                <span>🛡️ PayPal Official Gateway</span>
                                <span class="font-mono text-[10px]">Instant Activation</span>
                            </div>
                        </div>

                        <!-- Instant / Test Mode Option (Available in local/testing environment or for instant review) -->
                        <div @click="paymentMethod = 'simulated'"
                             :class="paymentMethod === 'simulated'
                                ? 'border-2 border-slate-600 bg-slate-100 dark:bg-slate-800 ring-2 ring-slate-400/20'
                                : 'border border-dashed border-slate-300 dark:border-slate-700 bg-white/50 dark:bg-slate-900/50 hover:border-slate-400'"
                             class="p-3.5 rounded-2xl cursor-pointer transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-lg">🧪</span>
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Developer / Demo Mode</span>
                                        <p class="text-[11px] text-slate-400">Simulate successful card payment instantly without external gateway</p>
                                    </div>
                                </div>
                                <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition"
                                      :class="paymentMethod === 'simulated' ? 'border-slate-700 bg-slate-700 text-white' : 'border-slate-300 dark:border-slate-600'">
                                    <span x-show="paymentMethod === 'simulated'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button with Dynamic Payment Method Label -->
                <button type="submit"
                        :class="{
                            'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-900/20': paymentMethod === 'stripe',
                            'bg-gradient-to-r from-[#00457C] to-[#0079C1] hover:from-[#003865] hover:to-[#0068a8] shadow-sky-900/20': paymentMethod === 'paypal',
                            'bg-slate-900 hover:bg-black dark:bg-slate-800 dark:hover:bg-slate-700 shadow-slate-900/20': paymentMethod === 'simulated'
                        }"
                        class="w-full py-4 rounded-2xl text-white font-extrabold text-sm shadow-lg transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                    <template x-if="paymentMethod === 'stripe'">
                        <span class="flex items-center gap-2">
                            <span>Proceed to Stripe Checkout</span>
                            <span class="text-base">→</span>
                        </span>
                    </template>
                    <template x-if="paymentMethod === 'paypal'">
                        <span class="flex items-center gap-2">
                            <span>Pay with PayPal</span>
                            <span class="text-base">→</span>
                        </span>
                    </template>
                    <template x-if="paymentMethod === 'simulated'">
                        <span class="flex items-center gap-2">
                            <span>Confirm & Activate Membership (Demo)</span>
                            <span class="text-base">✓</span>
                        </span>
                    </template>
                </button>

                <!-- Trust and Guarantee Badges -->
                <div class="pt-2 grid grid-cols-2 gap-2 text-center text-[10px] text-slate-500 dark:text-slate-400">
                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 flex items-center justify-center gap-1.5">
                        <span>⚡</span>
                        <span class="font-medium">Instant Activation</span>
                    </div>
                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 flex items-center justify-center gap-1.5">
                        <span>🔄</span>
                        <span class="font-medium">1-Click Cancel Anytime</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
