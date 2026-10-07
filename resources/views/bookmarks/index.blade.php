@extends('layouts.app')

@section('title', 'Shortlisted Profiles for Family Review - Nikah Connect')

@section('content')
<div class="py-10 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Top Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Pro Feature</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Family & Wali Consultation</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-heading">Shortlisted Candidates</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Save prospective suitors to review together with your parents, guardians, and Wali before reaching out.
                </p>
            </div>

            <a href="{{ route('discovery.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 font-bold text-xs transition flex items-center gap-1.5 border border-emerald-200/80 dark:border-emerald-800/80">
                <span>🔍</span> Find More Matches
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center gap-2">
                <span>✓</span> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-semibold flex items-center gap-2">
                <span>✕</span> {{ session('error') }}
            </div>
        @endif

        @if($isPro)
            @if($bookmarks->isEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-12 text-center space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-3xl">
                        ⭐
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Your Shortlist is Empty</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                        When browsing candidates in match discovery, click the bookmark icon on any card to save them here for consultation with your family.
                    </p>
                    <a href="{{ route('discovery.index') }}" class="inline-block px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                        Explore Candidate Profiles &rarr;
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($bookmarks as $b)
                        @php
                            $candidate = $b->bookmarkedUser;
                            $profile = $candidate->profile;
                        @endphp
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between"
                             x-data="{ editingNotes: false, notesVal: '{{ addslashes($b->notes ?? '') }}' }">
                            <div>
                                <!-- Top Card Header -->
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="relative w-14 h-14 rounded-2xl overflow-hidden bg-slate-800 shrink-0">
                                            @if($profile && $profile->primaryPhoto)
                                                <img src="{{ $profile->primaryPhoto->displayUrl() }}" alt="{{ $candidate->name }}"
                                                     class="w-full h-full object-cover {{ $b->is_photo_visible ? '' : 'photo-blur' }}">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-xl">
                                                    {{ substr($candidate->name, 0, 1) }}
                                                </div>
                                            @endif
                                            @if(!$b->is_photo_visible)
                                                <span class="absolute inset-0 flex items-center justify-center bg-black/40 text-[9px] text-white font-bold">🔒 Blurred</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $candidate->name }}</h3>
                                                @if($candidate->is_verified)
                                                    <svg class="w-3.5 h-3.5 text-blue-500 fill-current shrink-0" viewBox="0 0 20 20">
                                                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ $candidate->age }} yrs • {{ $profile?->city ?? 'Location not specified' }}
                                            </p>
                                            <p class="text-[11px] text-emerald-700 dark:text-emerald-400 font-semibold">
                                                {{ $profile?->sect_madhhab ?? 'Muslim' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if($b->compatibility_score)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                                            {{ $b->compatibility_score }}% Match
                                        </span>
                                    @endif
                                </div>

                                <!-- Private Family Consultation Notes -->
                                <div class="bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 rounded-xl p-3 my-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 flex items-center gap-1">
                                            <span>📝</span> Family Review Note
                                        </span>
                                        <button type="button" @click="editingNotes = !editingNotes" class="text-[10px] font-bold text-amber-700 dark:text-amber-400 hover:underline">
                                            <span x-text="editingNotes ? 'Close' : (notesVal ? 'Edit Note' : '+ Add Note')"></span>
                                        </button>
                                    </div>

                                    <!-- Read note -->
                                    <div x-show="!editingNotes" class="text-xs text-slate-700 dark:text-slate-300 italic min-h-[20px]">
                                        <span x-text="notesVal || 'No family notes added yet. Click edit to add Wali/family thoughts.'"></span>
                                    </div>

                                    <!-- Edit note form -->
                                    <div x-show="editingNotes" x-cloak class="mt-2">
                                        <form action="{{ route('bookmarks.notes', $b->id) }}" method="POST" class="space-y-2">
                                            @csrf
                                            <textarea name="notes" rows="2" x-model="notesVal" placeholder="e.g. Consult with Baba about relocation willingness..."
                                                      class="w-full p-2 text-xs rounded-lg border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white outline-none focus:ring-1 focus:ring-amber-500"></textarea>
                                            <div class="flex justify-end gap-1.5">
                                                <button type="button" @click="editingNotes = false" class="px-2.5 py-1 text-[10px] font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200/60 rounded-md">Cancel</button>
                                                <button type="submit" class="px-2.5 py-1 text-[10px] font-bold bg-amber-500 hover:bg-amber-600 text-white rounded-md">Save Note</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Actions -->
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800/60 flex items-center gap-2">
                                <a href="{{ route('discovery.show', $candidate->id) }}" class="flex-1 py-2 text-center rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition">
                                    View Full Profile
                                </a>

                                <a href="{{ route('profile.biodata.show', $candidate->id) }}" target="_blank" title="Print Matrimonial Biodata" class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition text-xs font-bold">
                                    🖨️
                                </a>

                                <form action="{{ route('bookmarks.toggle', $candidate->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" title="Remove from shortlist" class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/60 transition text-xs font-bold">
                                        ✕
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        @else
            <!-- Free User Upgrade Banner -->
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950 rounded-3xl p-8 sm:p-12 text-white shadow-xl border border-amber-800/40">
                <div class="max-w-2xl space-y-4">
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-400/30 inline-block">
                        ⭐ Seeker Premium Exclusive
                    </span>
                    <h2 class="text-3xl font-extrabold font-heading">
                        Shortlist Candidates for Family & Wali Review
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        In an Islamic marriage search, family consultation (*Mashwarah*) is key. Upgrade to Seeker Premium to shortlist candidates, write private family consultation notes, and download printable Shariah biodatas for your Wali!
                    </p>
                    <div class="pt-4 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('subscription.pricing') }}" class="px-7 py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs transition text-center shadow-lg">
                            Upgrade to Seeker Premium &rarr;
                        </a>
                        <a href="{{ route('discovery.index') }}" class="px-6 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs backdrop-blur-md transition text-center">
                            Browse Candidates
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
