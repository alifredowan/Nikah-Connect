<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $targetUser->name }} - Islamic Matrimonial Biodata | Nikah Connect</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Outfit', sans-serif; }
        .font-arabic { font-family: 'Amiri', serif; }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: #0f172a !important; }
            .biodata-container { box-shadow: none !important; border: 1px solid #cbd5e1 !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; padding: 20px !important; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen py-8">

    <!-- Action Toolbar (hidden when printed) -->
    <div class="no-print max-w-4xl mx-auto mb-6 px-4 flex items-center justify-between">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white text-slate-700 text-xs font-bold border border-slate-200 shadow-xs hover:bg-slate-50 transition">
            &larr; Back
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 font-medium">Ready for Family & Wali Consultation</span>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-md transition cursor-pointer">
                <span>🖨️</span> Print / Save as PDF
            </button>
        </div>
    </div>

    <!-- Main Printable Biodata Document Sheet -->
    <div class="biodata-container max-w-4xl mx-auto bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-12 space-y-8">

        <!-- Top Header & Bismillah -->
        <div class="text-center border-b border-emerald-900/10 pb-6 space-y-2">
            <div class="font-arabic text-2xl sm:text-3xl text-emerald-900 tracking-wider">
                بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
            </div>
            <p class="text-[11px] text-slate-500 uppercase tracking-widest font-bold">
                In the Name of Allah, the Most Gracious, the Most Merciful
            </p>
            <div class="pt-2 flex items-center justify-center gap-2">
                <span class="px-3 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Shariah Matrimonial Biodata
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-emerald-900">Nikah Connect Platform</span>
            </div>
        </div>

        <!-- Candidate Overview Box -->
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-200/80">
            <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl overflow-hidden bg-slate-900 border-2 border-emerald-700 shadow-md shrink-0 relative">
                @if($profile->primaryPhoto)
                    <img src="{{ $profile->primaryPhoto->displayUrl() }}" alt="{{ $targetUser->name }}"
                         class="w-full h-full object-cover {{ $isPhotoVisible ? '' : 'filter blur-[10px]' }}">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-3xl">
                        {{ substr($targetUser->name, 0, 1) }}
                    </div>
                @endif
                @if(!$isPhotoVisible)
                    <span class="absolute inset-0 flex items-center justify-center bg-black/40 text-[9px] text-white font-bold text-center px-1">
                        🔒 Photo Modesty Blurred
                    </span>
                @endif
            </div>

            <div class="flex-1 text-center sm:text-left space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <h1 class="text-2xl font-extrabold text-slate-900 font-heading">
                        {{ $targetUser->name }}
                    </h1>
                    @if($targetUser->is_verified)
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200 self-center sm:self-auto">
                            ✓ Verified Candidate
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-600 font-medium">
                    {{ $targetUser->age }} Years Old • {{ ucfirst($targetUser->gender) }} •
                    <span class="capitalize">{{ str_replace('_', ' ', $targetUser->marital_status ?? 'Single') }}</span>
                    @if($profile->height_cm)
                        • {{ $profile->height_cm }} cm ({{ floor($profile->height_cm / 30.48) }}'{{ round(($profile->height_cm / 2.54) % 12) }}")
                    @endif
                </p>

                <p class="text-xs text-slate-600">
                    📍 <strong>Location:</strong> {{ $profile->city ? $profile->city . ', ' : '' }}{{ $profile->country ?? 'Not specified' }}
                    @if($profile->citizenship)
                        (Citizenship: {{ $profile->citizenship }})
                    @endif
                </p>

                @if($profile->bio)
                    <div class="mt-2 text-xs text-slate-700 italic bg-white p-3 rounded-xl border border-slate-200">
                        &ldquo;{{ $profile->bio }}&rdquo;
                    </div>
                @endif
            </div>
        </div>

        <!-- Section 1: Islamic Practice & Deen Alignment -->
        <div class="space-y-3">
            <h2 class="text-sm font-extrabold text-emerald-950 font-heading uppercase tracking-wider flex items-center gap-2 border-b border-slate-200 pb-2">
                <span>🕌</span> 1. Religious Practice & Deen Alignment
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Sect / Madhhab</span>
                    <span class="font-bold text-slate-900">{{ $profile->sect_madhhab ?? 'Sunni' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Daily Prayer</span>
                    <span class="font-bold text-emerald-800">{{ str_replace('_', ' ', $profile->prayer_frequency ?? 'Practicing') }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Halal Diet</span>
                    <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->halal_dietary_adherence ?? 'Strict Halal') }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Quran Literacy</span>
                    <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->quran_knowledge ?? 'Regular Recitation') }}</span>
                </div>
                @if($targetUser->gender === 'female')
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block text-[10px] uppercase font-bold">Hijab / Modesty</span>
                        <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->hijab_niqab_practice ?? 'Practicing Hijab') }}</span>
                    </div>
                @else
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block text-[10px] uppercase font-bold">Sunnah Beard</span>
                        <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->beard_practice ?? 'Practicing Beard') }}</span>
                    </div>
                @endif
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Mosque Attendance</span>
                    <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->mosque_attendance ?? 'Regular') }}</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Education & Profession -->
        <div class="space-y-3">
            <h2 class="text-sm font-extrabold text-emerald-950 font-heading uppercase tracking-wider flex items-center gap-2 border-b border-slate-200 pb-2">
                <span>🎓</span> 2. Education & Profession
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Education Level</span>
                    <span class="font-bold text-slate-900">{{ $profile->education_level ?? 'Graduate' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Profession</span>
                    <span class="font-bold text-slate-900">{{ $profile->profession ?? 'Professional' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Employment Type</span>
                    <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->employment_type ?? 'Full-time') }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Mother Tongue</span>
                    <span class="font-bold text-slate-900">{{ $profile->mother_tongue ?? 'Bengali / English' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Family Structure Desired</span>
                    <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->desired_family_structure ?? 'Nuclear or Extended') }}</span>
                </div>
            </div>
        </div>

        <!-- Section 3: Family Background -->
        <div class="space-y-3">
            <h2 class="text-sm font-extrabold text-emerald-950 font-heading uppercase tracking-wider flex items-center gap-2 border-b border-slate-200 pb-2">
                <span>🏡</span> 3. Family Background
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Parents' Status</span>
                    <span class="font-bold text-slate-900">{{ $profile->parents_status ?? 'Living together' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Parents' Occupation</span>
                    <span class="font-bold text-slate-900">{{ $profile->parents_occupation ?? 'Respected family' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Siblings Count</span>
                    <span class="font-bold text-slate-900">{{ $profile->siblings_count ?? 'Not specified' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Family Religiosity</span>
                    <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $profile->family_religiosity ?? 'Practicing Sunni') }}</span>
                </div>
            </div>
        </div>

        <!-- Section 4: Partner Preferences & Expectations -->
        @php
            $prefs = $profile->partner_preferences ?? [];
        @endphp
        <div class="space-y-3">
            <h2 class="text-sm font-extrabold text-emerald-950 font-heading uppercase tracking-wider flex items-center gap-2 border-b border-slate-200 pb-2">
                <span>💍</span> 4. Desired Partner Preferences
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Preferred Age Range</span>
                    <span class="font-bold text-slate-900">
                        {{ ($prefs['age_min'] ?? null) && ($prefs['age_max'] ?? null) ? $prefs['age_min'] . ' - ' . $prefs['age_max'] . ' Years' : 'Compatible age' }}
                    </span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Preferred Sect</span>
                    <span class="font-bold text-slate-900">{{ $prefs['preferred_sect'] ?? 'Practicing Muslim' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Preferred Location</span>
                    <span class="font-bold text-slate-900">{{ $prefs['preferred_location'] ?? 'Open / Same country' }}</span>
                </div>
            </div>
        </div>

        <!-- Section 5: Islamic Guardian (Wali) Information -->
        <div class="space-y-3">
            <h2 class="text-sm font-extrabold text-emerald-950 font-heading uppercase tracking-wider flex items-center gap-2 border-b border-slate-200 pb-2">
                <span>🛡️</span> 5. Guardian (Wali) & Communication Protocol
            </h2>

            @if($activeWaliLink)
                <div class="bg-emerald-50/60 p-4 rounded-xl border border-emerald-200/80 text-xs space-y-1">
                    <div class="font-bold text-emerald-900">
                        Active Islamic Guardian Linked: {{ $activeWaliLink->wali_name }} ({{ ucfirst(str_replace('_', ' ', $activeWaliLink->relationship_type)) }})
                    </div>
                    <p class="text-emerald-800">
                        Contact: {{ $activeWaliLink->wali_phone ?? $activeWaliLink->wali_email }}
                    </p>
                    <p class="text-[11px] text-emerald-700">
                        * All matrimonial inquiries for this sister are chaperoned through her Wali according to Islamic protocol (FR-4.3).
                    </p>
                </div>
            @else
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs text-slate-600">
                    Candidate manages proposal communications directly with family consultation.
                </div>
            @endif
        </div>

        <!-- Bottom Footer & Quranic Verse -->
        <div class="pt-6 border-t border-slate-200 text-center space-y-2">
            <p class="font-arabic text-emerald-900 text-lg">
                وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً
            </p>
            <p class="text-[11px] text-slate-500 italic max-w-xl mx-auto">
                &ldquo;And of His signs is that He created for you from yourselves mates that you may find tranquility in them; and He placed between you affection and mercy.&rdquo; (Surah Ar-Rum: 21)
            </p>
            <p class="text-[10px] text-slate-400 pt-2 font-mono">
                Generated via Nikah Connect • Halal Islamic Matrimonial Platform • {{ now()->toFormattedDateString() }}
            </p>
        </div>

    </div>

</body>
</html>
