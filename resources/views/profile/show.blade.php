@extends('layouts.app')

@section('title', 'My Profile - Nikah Connect')

@section('content')
<div class="py-10 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Overview & Completeness Card (FR-2.4) -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800 overflow-hidden shrink-0 relative">
                        @if($profile->primaryPhoto)
                            <img src="{{ $profile->primaryPhoto->displayUrl() }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-2xl">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading">{{ $user->name }}</h1>
                            @if($user->is_verified)
                                <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                                    ✓ Verified Badge
                                </span>
                            @else
                                <span class="bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    Unverified (Submit KYC below)
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $user->email }} • {{ $user->age }} yrs • {{ $profile->city ?? 'Location not set' }}, {{ $profile->country ?? '' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('profile.edit') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                        ✏️ Edit Profile
                    </a>
                </div>
            </div>

            <!-- Profile Completeness Meter (FR-2.4) -->
            <div class="pt-6">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-slate-700 dark:text-slate-300">Profile Completeness (FR-2.4)</span>
                    <span class="font-extrabold text-emerald-700 dark:text-emerald-400">{{ $profile->completeness_percentage }}%</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $profile->completeness_percentage }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Complete your religious and family preferences to appear higher in daily compatibility recommendations.</p>
            </div>
        </div>

        <!-- Photos Section with Blurred Privacy (FR-2.2) -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">My Profile Photos ({{ $profile->photos->count() }}/6)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Photos remain blurred to candidates by default for modesty until mutual approval (FR-2.2).</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($profile->photos as $photo)
                    <div class="relative group rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 aspect-square bg-slate-800">
                        <img src="{{ $photo->displayUrl() }}" alt="Profile photo" class="w-full h-full object-cover">
                        @if($photo->is_primary)
                            <span class="absolute top-2 left-2 bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">Primary</span>
                        @endif
                        <!-- Action overlay -->
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center gap-2 p-2">
                            @if(!$photo->is_primary)
                                <form action="{{ route('profile.photos.primary', $photo->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[10px] bg-white text-slate-900 font-bold px-2 py-1 rounded">Make Primary</button>
                                </form>
                            @endif
                            <form action="{{ route('profile.photos.delete', $photo->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[10px] bg-rose-600 text-white font-bold px-2 py-1 rounded">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                @if($profile->photos->count() < 6)
                    <!-- Upload Photo Card -->
                    <form action="{{ route('profile.photos.upload') }}" method="POST" enctype="multipart/form-data" class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl aspect-square flex flex-col items-center justify-center p-3 text-center hover:bg-slate-50 dark:hover:bg-slate-800/60 transition cursor-pointer relative">
                        @csrf
                        <input type="file" name="photo" required accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="this.form.submit()">
                        <span class="text-2xl text-slate-400 mb-1">+</span>
                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Upload Photo</span>
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 mt-0.5">Max 5MB</span>
                    </form>
                @endif
            </div>
        </div>

        <!-- KYC Verification Section (FR-1.4) -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🪪</span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">Government ID & KYC Verification (FR-1.4)</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Earn the trusted "Verified" badge to build authenticity and peace of mind.</p>
                    </div>
                </div>
                @if($user->is_verified)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300">
                        ✓ Verified Account
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300">
                        Pending Verification
                    </span>
                @endif
            </div>

            @if(!$user->is_verified)
                <form action="{{ route('profile.verification.submit') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Document Type</label>
                        <select name="document_type" required class="w-full p-2.5 rounded-xl border border-slate-300 dark:border-slate-700 outline-none bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            <option value="passport">International Passport</option>
                            <option value="national_id">National ID Card</option>
                            <option value="driving_license">Driver's License</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Upload Document (Front)</label>
                        <input type="file" name="document" required accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full p-2 rounded-xl border border-slate-300 dark:border-slate-700 outline-none bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Selfie Verification</label>
                        <input type="file" name="selfie" accept="image/*"
                               class="w-full p-2 rounded-xl border border-slate-300 dark:border-slate-700 outline-none bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    </div>
                    <div class="sm:col-span-3 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition">
                            Submit for KYC Verification
                        </button>
                    </div>
                </form>
            @else
                <p class="text-xs text-slate-600 dark:text-slate-300">Alhamdulillah, your identity documents have been verified by our moderation team.</p>
            @endif
        </div>

        <!-- Account Deactivation & GDPR Right to Erasure (FR-1.6) -->
        <div class="bg-rose-50/60 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-3xl p-6">
            <h3 class="text-sm font-bold text-rose-900 dark:text-rose-200 font-heading mb-1">Deactivation & GDPR Right to Erasure (FR-1.6)</h3>
            <p class="text-xs text-rose-800/80 dark:text-rose-300/80 mb-4">You may deactivate your account or request complete data deletion at any time under GDPR privacy protections.</p>
            <form action="{{ route('account.deactivate') }}" method="POST" onsubmit="return confirm('Are you sure you want to deactivate your profile?');" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="text" name="reason" required placeholder="Reason for deactivation (e.g. Found spouse alhamdulillah, taking a break)"
                       class="flex-1 px-4 py-2 rounded-xl border border-rose-300 dark:border-rose-800 text-xs focus:ring-2 focus:ring-rose-500 outline-none bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shrink-0">
                    Deactivate Account
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
