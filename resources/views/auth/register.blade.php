@extends('layouts.app')

@section('title', 'Register for Halal Matrimony - Nikah Connect')

@section('content')
<div class="py-12 bg-slate-50">
    <div class="max-w-xl mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
            <div class="text-center mb-8">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl mx-auto mb-3">
                    🧕
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 font-heading">Create Halal Matrimonial Profile</h1>
                <p class="text-xs text-slate-500 mt-1">Begin your journey towards a blessed marriage with sincere intentions.</p>
            </div>

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Legal Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                           placeholder="e.g. Zayd Al-Hassan">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                               placeholder="your.email@domain.com">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone (with country code)</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                               placeholder="+1 555 123 4567">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Gender (FR-1.3)</label>
                        <select name="gender" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Brother (Groom)</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Sister (Bride)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Date of Birth (18+ Enforced)
                        </label>
                        <input type="date" name="dob" value="{{ old('dob') }}" required
                               max="{{ \Carbon\Carbon::now()->subYears(18)->toDateString() }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                        <span class="text-[10px] text-slate-400 block mt-0.5">You must be at least 18 years old.</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Marital Status (FR-1.3)</label>
                    <select name="marital_status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                        <option value="">Select Marital Status</option>
                        <option value="never_married" {{ old('marital_status') === 'never_married' ? 'selected' : '' }}>Single / Never Married</option>
                        <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="widowed" {{ old('marital_status') === 'widowed' ? 'selected' : '' }}>Widowed</option>
                        <option value="annulled" {{ old('marital_status') === 'annulled' ? 'selected' : '' }}>Annulled</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                               placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-start gap-2.5 text-xs text-slate-600">
                        <input type="checkbox" name="terms" required class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                        <span>
                            I affirm that I am using this platform strictly for marriage (Nikah), I agree to conduct interactions with Islamic etiquette, and I accept the <a href="#" class="text-emerald-700 underline">Terms of Service</a> and <a href="#" class="text-emerald-700 underline">Privacy Policy</a> (GDPR compliant).
                        </span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-900/15 transition mt-4">
                    Create My Profile
                </button>
            </form>

            <div class="text-center mt-6 pt-6 border-t border-slate-100 text-xs text-slate-500">
                Already registered? <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:text-emerald-800">Sign in to your account</a>
            </div>
        </div>
    </div>
</div>
@endsection
