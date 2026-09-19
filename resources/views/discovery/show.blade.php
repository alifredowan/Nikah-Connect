@extends('layouts.app')

@section('title', $targetUser->name . ' - Islamic Matrimonial Profile')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Back Button & Breadcrumbs -->
        <div>
            <a href="{{ route('discovery.index') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700 flex items-center gap-1">
                &larr; Back to match search
            </a>
        </div>

        <!-- Top Profile Banner Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="h-32 bg-emerald-gradient relative">
                @if($compatibilityScore)
                    <div class="absolute top-4 right-6 bg-white/90 backdrop-blur-md px-3.5 py-1.5 rounded-full shadow-sm flex items-center gap-2 text-xs font-bold text-emerald-950">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        {{ $compatibilityScore }}% Deen Compatibility (FR-6.4)
                    </div>
                @endif
            </div>
            <div class="px-6 pb-6 pt-0 relative">
                <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4 -mt-16 mb-6">
                    <div class="flex items-end gap-4">
                        <div class="w-28 h-28 rounded-2xl overflow-hidden border-4 border-white bg-slate-800 shadow-lg relative shrink-0">
                            @if($profile->primaryPhoto)
                                <img src="{{ $profile->primaryPhoto->displayUrl() }}" alt="{{ $targetUser->name }}"
                                     class="w-full h-full object-cover {{ $isPhotoVisible ? '' : 'photo-blur' }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-3xl">
                                    {{ substr($targetUser->name, 0, 1) }}
                                </div>
                            @endif
                            @if(!$isPhotoVisible)
                                <div class="absolute inset-0 flex items-center justify-center bg-black/40 text-[10px] text-white font-bold text-center px-1">
                                    🔒 Photo Blurred (FR-2.2)
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-extrabold text-slate-900 font-heading">{{ $targetUser->name }}</h1>
                                @if($targetUser->is_verified)
                                    <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1" title="Government ID Verified (FR-1.4)">
                                        ✓ Verified Candidate
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 font-medium">
                                {{ $targetUser->age }} years • {{ $profile->city }}, {{ $profile->country }} •
                                <span class="capitalize">{{ str_replace('_', ' ', $targetUser->marital_status) }}</span>
                            </p>
                            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    {{ $profile->sect_madhhab ?? 'Sunni' }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    🕌 {{ str_replace('_', ' ', $profile->prayer_frequency ?? 'Practicing') }}
                                </span>
                                @if($profile->wali_required)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 flex items-center gap-1">
                                        🛡️ Guardian (Wali) Chaperoned
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Interest Action Button -->
                    <div class="w-full sm:w-auto">
                        @if($existingInterest)
                            <div class="px-4 py-2.5 rounded-xl text-xs font-bold text-center {{ $existingInterest->status === 'accepted' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                @if($existingInterest->status === 'accepted')
                                    ✓ Interest Accepted
                                    @if($existingInterest->conversation)
                                        <a href="{{ route('chat.show', $existingInterest->conversation->id) }}" class="underline ml-2 text-emerald-900">Open Messages &rarr;</a>
                                    @endif
                                @elseif($existingInterest->status === 'pending')
                                    ⏳ Interest Request Pending
                                @else
                                    Status: {{ ucfirst($existingInterest->status) }}
                                @endif
                            </div>
                        @else
                            <button type="button" onclick="document.getElementById('sendInterestModal').classList.remove('hidden')" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-900/15 transition flex items-center justify-center gap-2">
                                <span>💌</span> Express Halal Interest
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Photo Privacy Notice -->
                @if(!$isPhotoVisible)
                    <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2.5 text-amber-900">
                            <span class="text-xl">🔒</span>
                            <div>
                                <strong class="block text-amber-950 font-bold">Islamic Photo Privacy Controls Active (FR-2.2)</strong>
                                <span>Photos are blurred by default until mutual interest is accepted or explicit reciprocal photo permission is granted.</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Columns: Bio, Deen & Career -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Bio Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-3">About Me & Intentions</h3>
                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $profile->bio ?? 'No biography provided yet.' }}
                    </p>
                </div>

                <!-- Religious Practice & Islamic Alignment (FR-2.1, 6.4) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                        <span class="text-xl">🕌</span>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Islamic Practice & Values</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <span class="text-slate-500 block mb-0.5">Sect / School of Jurisprudence:</span>
                            <span class="font-bold text-slate-900">{{ $profile->sect_madhhab ?? 'Sunni' }}</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <span class="text-slate-500 block mb-0.5">Prayer Frequency:</span>
                            <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->prayer_frequency ?? 'Regularly') }}</span>
                        </div>
                        @if($targetUser->gender === 'female')
                            <div class="bg-slate-50 p-3 rounded-xl">
                                <span class="text-slate-500 block mb-0.5">Hijab / Modesty Practice:</span>
                                <span class="font-bold text-slate-900">{{ ucfirst($profile->hijab_niqab_practice ?? 'Observes Modesty') }}</span>
                            </div>
                        @else
                            <div class="bg-slate-50 p-3 rounded-xl">
                                <span class="text-slate-500 block mb-0.5">Sunnah Beard Practice:</span>
                                <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->beard_practice ?? 'Maintained') }}</span>
                            </div>
                        @endif
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <span class="text-slate-500 block mb-0.5">Quran Knowledge:</span>
                            <span class="font-bold text-slate-900">{{ ucfirst(str_replace('_', ' ', $profile->quran_knowledge ?? 'Fluent Reader')) }}</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <span class="text-slate-500 block mb-0.5">Halal Dietary Adherence:</span>
                            <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->halal_dietary_adherence ?? 'Strictly Halal') }}</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <span class="text-slate-500 block mb-0.5">Mosque Attendance:</span>
                            <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->mosque_attendance ?? 'Weekly Jummah') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Education & Professional Background -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                        <span class="text-xl">🎓</span>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Education & Career</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <span class="text-slate-500 block mb-0.5">Highest Education:</span>
                            <span class="font-bold text-slate-900">{{ $profile->education_level ?? 'Graduate' }}</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <span class="text-slate-500 block mb-0.5">Profession / Job Title:</span>
                            <span class="font-bold text-slate-900">{{ $profile->profession ?? 'Professional' }}</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <span class="text-slate-500 block mb-0.5">Employment Type:</span>
                            <span class="font-bold text-slate-900">{{ $profile->employment_type ?? 'Full-time' }}</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <span class="text-slate-500 block mb-0.5">Height:</span>
                            <span class="font-bold text-slate-900">{{ $profile->height_cm ? $profile->height_cm . ' cm' : 'Not specified' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Family & Partner Preferences -->
            <div class="space-y-6">
                <!-- Family Background -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                        <span class="text-xl">🏡</span>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Family Background</h3>
                    </div>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-slate-500">Family Religiosity:</span>
                            <span class="font-bold text-slate-900">{{ ucfirst(str_replace('_', ' ', $profile->family_religiosity ?? 'Practicing')) }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-slate-500">Desired Living Structure:</span>
                            <span class="font-bold text-slate-900">{{ ucfirst($profile->desired_family_structure ?? 'Independent') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-slate-500">Siblings:</span>
                            <span class="font-bold text-slate-900">{{ $profile->siblings_count }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Parents' Occupation:</span>
                            <span class="font-bold text-slate-900">{{ $profile->parents_occupation ?? 'Respected background' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Partner Preferences -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                        <span class="text-xl">🎯</span>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Looking For</h3>
                    </div>
                    @php $prefs = $profile->partner_preferences ?? []; @endphp
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-slate-500">Preferred Age:</span>
                            <span class="font-bold text-slate-900">
                                {{ $prefs['age_min'] ?? 'Any' }} - {{ $prefs['age_max'] ?? 'Any' }} years
                            </span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-slate-500">Preferred Sect:</span>
                            <span class="font-bold text-slate-900">{{ $prefs['preferred_sect'] ?? 'Compatible Sunni' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Preferred Location:</span>
                            <span class="font-bold text-slate-900">{{ $prefs['preferred_location'] ?? 'Open' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Report User (FR-4.6) -->
                <div class="text-center pt-2">
                    <button type="button" onclick="document.getElementById('reportModal').classList.remove('hidden')" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">
                        🚩 Report inappropriate profile or behavior (FR-4.6)
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Send Interest Modal -->
<div id="sendInterestModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-slate-900 mb-2 font-heading">Express Halal Interest</h3>
        <p class="text-xs text-slate-500 mb-4">
            @if($profile->wali_required)
                Notice: This candidate has a linked guardian (Wali). If accepted, communications will be chaperoned according to Islamic protocol (FR-4.3).
            @else
                Express your interest respectfully. If the candidate accepts, private halal messaging will unlock (FR-4.2).
            @endif
        </p>
        <form action="{{ route('interests.send', $targetUser->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1">Optional Introductory Note</label>
                <textarea name="note" rows="3" class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="As-salamu alaykum, I appreciate your profile and shared deen values..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('sendInterestModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs">Send Interest</button>
            </div>
        </form>
    </div>
</div>

<!-- Report User Modal (FR-4.6) -->
<div id="reportModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-slate-900 mb-2 font-heading">Report Candidate</h3>
        <form action="{{ route('reports.store') }}" method="POST">
            @csrf
            <input type="hidden" name="reported_id" value="{{ $targetUser->id }}">
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Report</label>
                <select name="reason" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="inappropriate_language">Inappropriate or vulgar language</option>
                    <option value="harassment">Harassment / pressure</option>
                    <option value="fake_profile">Suspicious / fake profile claims</option>
                    <option value="external_solicitation">Commercial solicitation / scam</option>
                    <option value="contact_sharing_violation">Unauthorized external contact sharing</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1">Details for Moderation Staff (FR-7.2)</label>
                <textarea name="details" rows="3" class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="Provide any context..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('reportModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700">Submit Report</button>
            </div>
        </form>
    </div>
</div>
@endsection
