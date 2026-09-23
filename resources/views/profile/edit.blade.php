@extends('layouts.app')

@section('title', 'Edit Profile - Nikah Connect')

@section('content')
<div class="py-10 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white font-heading">Edit Islamic & Personal Profile</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Detailed Islamic preferences help our algorithm rank compatible candidates for you (FR-2.1, 6.4).</p>
            </div>
            <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400">
                Cancel & Return
            </a>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- 1. Religious & Practice Alignment (FR-2.1, 6.4) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xl">🕌</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">Islamic Practice & Values (Highest Compatibility Weight)</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Sect / Madhhab</label>
                        <select name="sect_madhhab" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">Select Sect</option>
                            <option value="Sunni - Hanafi" {{ old('sect_madhhab', $profile->sect_madhhab) === 'Sunni - Hanafi' ? 'selected' : '' }}>Sunni - Hanafi</option>
                            <option value="Sunni - Shafi'i" {{ old('sect_madhhab', $profile->sect_madhhab) === 'Sunni - Shafi\'i' ? 'selected' : '' }}>Sunni - Shafi'i</option>
                            <option value="Sunni - Maliki" {{ old('sect_madhhab', $profile->sect_madhhab) === 'Sunni - Maliki' ? 'selected' : '' }}>Sunni - Maliki</option>
                            <option value="Sunni - Hanbali" {{ old('sect_madhhab', $profile->sect_madhhab) === 'Sunni - Hanbali' ? 'selected' : '' }}>Sunni - Hanbali</option>
                            <option value="Shia - Jafari" {{ old('sect_madhhab', $profile->sect_madhhab) === 'Shia - Jafari' ? 'selected' : '' }}>Shia - Jafari</option>
                            <option value="Muslim (Unspecified)" {{ old('sect_madhhab', $profile->sect_madhhab) === 'Muslim (Unspecified)' ? 'selected' : '' }}>Just Muslim</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Daily Prayer (Salah) Frequency</label>
                        <select name="prayer_frequency" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">Select Frequency</option>
                            <option value="5x_daily" {{ old('prayer_frequency', $profile->prayer_frequency) === '5x_daily' ? 'selected' : '' }}>Always 5x Daily Prayer</option>
                            <option value="occasional" {{ old('prayer_frequency', $profile->prayer_frequency) === 'occasional' ? 'selected' : '' }}>Most Prayers / Striving</option>
                            <option value="rarely" {{ old('prayer_frequency', $profile->prayer_frequency) === 'rarely' ? 'selected' : '' }}>Only Friday / Eid</option>
                        </select>
                    </div>

                    @if($user->gender === 'female')
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Hijab / Modesty Practice</label>
                            <select name="hijab_niqab_practice" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="">Select Practice</option>
                                <option value="hijab" {{ old('hijab_niqab_practice', $profile->hijab_niqab_practice) === 'hijab' ? 'selected' : '' }}>Observes Hijab</option>
                                <option value="niqab" {{ old('hijab_niqab_practice', $profile->hijab_niqab_practice) === 'niqab' ? 'selected' : '' }}>Observes Niqab</option>
                                <option value="modest_without_hijab" {{ old('hijab_niqab_practice', $profile->hijab_niqab_practice) === 'modest_without_hijab' ? 'selected' : '' }}>Modest without Hijab</option>
                                <option value="planning_to_wear" {{ old('hijab_niqab_practice', $profile->hijab_niqab_practice) === 'planning_to_wear' ? 'selected' : '' }}>Planning to wear in future</option>
                            </select>
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Sunnah Beard Practice</label>
                            <select name="beard_practice" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="">Select Practice</option>
                                <option value="full_sunnah_beard" {{ old('beard_practice', $profile->beard_practice) === 'full_sunnah_beard' ? 'selected' : '' }}>Full Sunnah Beard</option>
                                <option value="trimmed_beard" {{ old('beard_practice', $profile->beard_practice) === 'trimmed_beard' ? 'selected' : '' }}>Trimmed / Neat Beard</option>
                                <option value="clean_shaven" {{ old('beard_practice', $profile->beard_practice) === 'clean_shaven' ? 'selected' : '' }}>Clean Shaven</option>
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Quran Knowledge</label>
                        <select name="quran_knowledge" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">Select Quran Memorization</option>
                            <option value="hafiz" {{ old('quran_knowledge', $profile->quran_knowledge) === 'hafiz' ? 'selected' : '' }}>Hafiz / Hafiza (Complete Memorization)</option>
                            <option value="fluent_reciter" {{ old('quran_knowledge', $profile->quran_knowledge) === 'fluent_reciter' ? 'selected' : '' }}>Fluent Reciter with Tajweed</option>
                            <option value="basic_reader" {{ old('quran_knowledge', $profile->quran_knowledge) === 'basic_reader' ? 'selected' : '' }}>Basic Arabic Reader</option>
                            <option value="learning" {{ old('quran_knowledge', $profile->quran_knowledge) === 'learning' ? 'selected' : '' }}>Currently Learning</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Halal Dietary Adherence</label>
                        <select name="halal_dietary_adherence" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="strictly_halal" {{ old('halal_dietary_adherence', $profile->halal_dietary_adherence) === 'strictly_halal' ? 'selected' : '' }}>Strictly Halal (Zabiha only)</option>
                            <option value="halal_meat_only" {{ old('halal_dietary_adherence', $profile->halal_dietary_adherence) === 'halal_meat_only' ? 'selected' : '' }}>Halal meat / Seafood</option>
                            <option value="flexible" {{ old('halal_dietary_adherence', $profile->halal_dietary_adherence) === 'flexible' ? 'selected' : '' }}>Flexible</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Mosque Attendance</label>
                        <select name="mosque_attendance" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="daily" {{ old('mosque_attendance', $profile->mosque_attendance) === 'daily' ? 'selected' : '' }}>Daily Prayers</option>
                            <option value="weekly_jummah" {{ old('mosque_attendance', $profile->mosque_attendance) === 'weekly_jummah' ? 'selected' : '' }}>Weekly Jummah</option>
                            <option value="occasional" {{ old('mosque_attendance', $profile->mosque_attendance) === 'occasional' ? 'selected' : '' }}>Occasional</option>
                        </select>
                    </div>
                </div>

                <!-- Wali Requirement Checkbox -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <label class="flex items-start gap-3 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="wali_required" value="1" {{ old('wali_required', $profile->wali_required) ? 'checked' : '' }} class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <strong class="text-slate-900 dark:text-white block font-bold">🛡️ Require Guardian (Wali) Involvement (FR-4.3)</strong>
                            <span class="text-slate-500 dark:text-slate-400">When enabled, suitors cannot unlock direct private messaging with you until your linked guardian confirms approval.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 2. Personal, Education & Career Details -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xl">🎓</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">Personal, Education & Profession</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Height (cm)</label>
                        <input type="number" name="height_cm" value="{{ old('height_cm', $profile->height_cm) }}" min="120" max="230" placeholder="e.g. 175"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city', $profile->city) }}" placeholder="e.g. London"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Country</label>
                        <input type="text" name="country" value="{{ old('country', $profile->country) }}" placeholder="e.g. United Kingdom"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Education Level</label>
                        <input type="text" name="education_level" value="{{ old('education_level', $profile->education_level) }}" placeholder="e.g. Master in Data Science"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Profession</label>
                        <input type="text" name="profession" value="{{ old('profession', $profile->profession) }}" placeholder="e.g. Cloud Engineer"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Bio / Sincere Marriage Intentions</label>
                    <textarea name="bio" rows="4" class="w-full p-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="Describe your religious commitment, family background, and what you look for in a blessed marriage...">{{ old('bio', $profile->bio) }}</textarea>
                </div>
            </div>

            <!-- 3. Family Background & Living Structure -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xl">🏡</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">Family Background</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Family Religiosity</label>
                        <select name="family_religiosity" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="very_practicing" {{ old('family_religiosity', $profile->family_religiosity) === 'very_practicing' ? 'selected' : '' }}>Very Practicing</option>
                            <option value="moderate" {{ old('family_religiosity', $profile->family_religiosity) === 'moderate' ? 'selected' : '' }}>Moderate</option>
                            <option value="traditional" {{ old('family_religiosity', $profile->family_religiosity) === 'traditional' ? 'selected' : '' }}>Traditional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Desired Living Structure</label>
                        <select name="desired_family_structure" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="independent" {{ old('desired_family_structure', $profile->desired_family_structure) === 'independent' ? 'selected' : '' }}>Independent Household</option>
                            <option value="joint_family" {{ old('desired_family_structure', $profile->desired_family_structure) === 'joint_family' ? 'selected' : '' }}>Joint Family Structure</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Number of Siblings</label>
                        <input type="number" name="siblings_count" value="{{ old('siblings_count', $profile->siblings_count ?? 0) }}" min="0" max="20"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- 4. Partner Preferences (Looking For) -->
            @php $prefs = $profile->partner_preferences ?? []; @endphp
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xl">🎯</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">Partner Preferences (Algorithmic Targeting)</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Preferred Age Range</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="pref_age_min" value="{{ old('pref_age_min', $prefs['age_min'] ?? 20) }}" min="18" max="80" placeholder="Min"
                                   class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="text-slate-400 dark:text-slate-500 text-xs">to</span>
                            <input type="number" name="pref_age_max" value="{{ old('pref_age_max', $prefs['age_max'] ?? 35) }}" min="18" max="80" placeholder="Max"
                                   class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Preferred Location</label>
                        <input type="text" name="pref_location" value="{{ old('pref_location', $prefs['preferred_location'] ?? '') }}" placeholder="e.g. United Kingdom / Open"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3 pt-2">
                <button type="submit" class="px-8 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-900/15 transition">
                    Save Profile Changes
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
