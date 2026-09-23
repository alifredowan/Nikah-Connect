@extends('layouts.app')

@section('title', 'Register - Nikah Connect')

@section('content')
<div class="py-12 bg-slate-50 dark:bg-slate-950 min-h-screen" x-data="{ role: '{{ old('role', 'seeker') }}' }">
    <div class="max-w-xl mx-auto px-4 sm:px-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-8 transition-colors">
            <div class="text-center mb-8">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 flex items-center justify-center text-2xl mx-auto mb-3"
                     x-text="role === 'seeker' ? '💍' : '🛡️'">
                    💍
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white font-heading">
                    <span x-show="role === 'seeker'">Create Candidate Profile</span>
                    <span x-show="role === 'wali'" style="display: none;">Register as Guardian (Wali)</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <span x-show="role === 'seeker'">Begin your journey towards a blessed marriage with sincere intentions.</span>
                    <span x-show="role === 'wali'" style="display: none;">Safeguard and assist your family member in their marriage search.</span>
                </p>
            </div>

            <!-- Role Selection Tabs -->
            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">I am registering as:</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" @click="role = 'seeker'"
                            :class="role === 'seeker' ? 'border-emerald-600 bg-emerald-50/70 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-200 ring-2 ring-emerald-500' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'"
                            class="p-4 rounded-xl border text-left transition flex flex-col justify-between">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">💍</span>
                            <span class="font-bold text-sm font-heading">Candidate / Seeker</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">I am looking for a spouse for myself (Groom or Bride).</p>
                    </button>

                    <button type="button" @click="role = 'wali'"
                            :class="role === 'wali' ? 'border-purple-600 bg-purple-50/70 dark:bg-purple-950/60 text-purple-900 dark:text-purple-200 ring-2 ring-purple-500' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'"
                            class="p-4 rounded-xl border text-left transition flex flex-col justify-between">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">🛡️</span>
                            <span class="font-bold text-sm font-heading">Guardian / Wali</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">I am registering on behalf of my daughter, sister, or ward.</p>
                    </button>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Hidden Input for Selected Role -->
                <input type="hidden" name="role" :value="role">

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                        <span x-show="role === 'seeker'">Full Legal Name</span>
                        <span x-show="role === 'wali'" style="display: none;">Guardian's Full Legal Name</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                           :placeholder="role === 'seeker' ? 'e.g. Zayd Al-Hassan' : 'e.g. Dr. Tariq Al-Mansoor'">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                               placeholder="your.email@domain.com">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                               placeholder="+1 555 123 4567">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Gender</label>
                        <select name="gender" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Brother (Male)</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Sister (Female)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Date of Birth (18+ Enforced)
                        </label>
                        <input type="date" name="dob" value="{{ old('dob') }}" required
                               max="{{ \Carbon\Carbon::now()->subYears(18)->toDateString() }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                        <span class="text-[10px] text-slate-400 block mt-0.5">Must be at least 18 years old.</span>
                    </div>
                </div>

                <!-- Seeker-specific Field: Marital Status -->
                <div x-show="role === 'seeker'">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Marital Status</label>
                    <select name="marital_status" :required="role === 'seeker'" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                        <option value="">Select Marital Status</option>
                        <option value="never_married" {{ old('marital_status', 'never_married') === 'never_married' ? 'selected' : '' }}>Single / Never Married</option>
                        <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="widowed" {{ old('marital_status') === 'widowed' ? 'selected' : '' }}>Widowed</option>
                        <option value="annulled" {{ old('marital_status') === 'annulled' ? 'selected' : '' }}>Annulled</option>
                    </select>
                </div>

                <!-- Wali-specific Field: Relationship to Ward -->
                <div x-show="role === 'wali'" style="display: none;">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Relationship to Candidate / Ward</label>
                    <select name="relationship_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                        <option value="father" {{ old('relationship_type') === 'father' ? 'selected' : '' }}>Father</option>
                        <option value="brother" {{ old('relationship_type') === 'brother' ? 'selected' : '' }}>Brother</option>
                        <option value="uncle" {{ old('relationship_type') === 'uncle' ? 'selected' : '' }}>Uncle (Paternal / Maternal)</option>
                        <option value="grandfather" {{ old('relationship_type') === 'grandfather' ? 'selected' : '' }}>Grandfather</option>
                        <option value="other_mahram" {{ old('relationship_type') === 'other_mahram' ? 'selected' : '' }}>Other Mahram / Legal Guardian</option>
                    </select>
                    <span class="text-[10px] text-slate-400 block mt-1">You can easily link your ward's profile once logged in.</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Password</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                               placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-start gap-2.5 text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                        <input type="checkbox" name="terms" required class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 dark:bg-slate-800 dark:border-slate-700">
                        <span>
                            I affirm that I am using this platform strictly for marriage (Nikah), I agree to conduct interactions with Islamic etiquette, and I accept the <a href="#" class="text-emerald-700 dark:text-emerald-400 underline font-medium">Terms of Service</a> and <a href="#" class="text-emerald-700 dark:text-emerald-400 underline font-medium">Privacy Policy</a>.
                        </span>
                    </label>
                </div>

                <button type="submit"
                        :class="role === 'seeker' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-900/15' : 'bg-purple-700 hover:bg-purple-800 shadow-purple-900/15'"
                        class="w-full py-3.5 rounded-xl text-white font-bold text-sm shadow-md transition mt-4">
                    <span x-show="role === 'seeker'">Create Candidate Profile</span>
                    <span x-show="role === 'wali'" style="display: none;">Create Guardian (Wali) Account</span>
                </button>
            </form>

            <div class="text-center mt-6 pt-4 text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800">
                Already registered? <a href="{{ route('login') }}" class="font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300">Sign in here</a>
            </div>
        </div>
    </div>
</div>
@endsection
