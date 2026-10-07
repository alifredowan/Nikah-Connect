@extends('layouts.app')

@section('title', 'Nikah Mubarak - Celebration & Milestone - Nikah Connect')

@section('content')
<div class="py-12 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

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

        @if(session('info'))
            <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 text-xs font-semibold flex items-center gap-2">
                <span>ℹ️</span> {{ session('info') }}
            </div>
        @endif

        <!-- Mubarak Hero Celebration Card -->
        <div class="relative overflow-hidden bg-gradient-to-br from-emerald-950 via-slate-900 to-emerald-900 rounded-3xl p-8 sm:p-12 text-white shadow-2xl border border-emerald-700/40 text-center space-y-6">
            <!-- Islamic Dua -->
            <div class="space-y-3 max-w-2xl mx-auto">
                <span class="px-3.5 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-400/30 inline-block">
                    💍 Sacred Nikah Milestone
                </span>

                <div class="font-arabic text-2xl sm:text-3xl text-amber-200 tracking-wider pt-2 leading-relaxed">
                    بَارَكَ اللَّهُ لَكَ وَبَارَكَ عَلَيْكَ وَجَمَعَ بَيْنَكُمَا فِي خَيْرٍ
                </div>

                <p class="text-xs sm:text-sm text-slate-300 italic">
                    &ldquo;May Allah bless you, and bestow His blessings upon you, and join you together in goodness.&rdquo; (Sunan Abi Dawud)
                </p>
            </div>

            <!-- Main Title -->
            <div>
                <h1 class="text-3xl sm:text-4xl font-extrabold font-heading text-white tracking-tight">
                    @if($marriage->isConfirmed())
                        Alhamdulillah, Nikah Mubarak!
                    @else
                        Nikah Milestone in Confirmation
                    @endif
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-2 max-w-xl mx-auto">
                    @if($marriage->isConfirmed())
                        May Allah fill your blessed union with mutual affection, patience, tranquility, and righteousness.
                    @else
                        A blessed marriage proposal has been declared and awaits mutual confirmation.
                    @endif
                </p>
            </div>

            <!-- Couple Card Visual -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 max-w-xl mx-auto border border-white/15 flex items-center justify-around gap-4">
                <div class="text-center space-y-2">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl bg-emerald-800 border-2 border-amber-400 overflow-hidden shadow-lg flex items-center justify-center text-2xl font-bold">
                        @if($marriage->groom->profile?->primaryPhoto)
                            <img src="{{ $marriage->groom->profile->primaryPhoto->displayUrl() }}" alt="{{ $marriage->groom->name }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($marriage->groom->name, 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-amber-300 block">Groom</span>
                        <strong class="text-sm font-heading block truncate max-w-[120px]">{{ $marriage->groom->name }}</strong>
                    </div>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-amber-400 text-slate-950 flex items-center justify-center text-xl shadow-md">
                        💍
                    </div>
                    <span class="text-[10px] font-extrabold uppercase text-amber-300 tracking-wider mt-1">United</span>
                </div>

                <div class="text-center space-y-2">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl bg-emerald-800 border-2 border-amber-400 overflow-hidden shadow-lg flex items-center justify-center text-2xl font-bold">
                        @if($marriage->bride->profile?->primaryPhoto)
                            <img src="{{ $marriage->bride->profile->primaryPhoto->displayUrl() }}" alt="{{ $marriage->bride->name }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($marriage->bride->name, 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-amber-300 block">Bride</span>
                        <strong class="text-sm font-heading block truncate max-w-[120px]">{{ $marriage->bride->name }}</strong>
                    </div>
                </div>
            </div>

            <!-- Pending Confirmation Prompt (if applicable) -->
            @if($marriage->isPending())
                <div class="max-w-lg mx-auto bg-amber-500/20 border border-amber-400/40 rounded-2xl p-5 text-center space-y-3">
                    @if($user->id !== $marriage->initiated_by_user_id)
                        <span class="text-xs font-bold text-amber-300 block">
                            🔔 {{ $marriage->initiator->name }} has declared that you both have completed your Nikah!
                        </span>
                        <p class="text-xs text-slate-300">
                            Confirming will finalize your marriage milestone, update your status to Married, and close past inquiries.
                        </p>
                        <div class="flex items-center justify-center gap-3 pt-1">
                            <form action="{{ route('marriages.confirm', $marriage->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-md transition cursor-pointer">
                                    ✓ Yes, Confirm Nikah!
                                </button>
                            </form>
                            <form action="{{ route('marriages.decline', $marriage->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 text-xs font-bold transition cursor-pointer">
                                    ✕ Decline
                                </button>
                            </form>
                        </div>
                    @else
                        <span class="text-xs font-bold text-amber-300 block">
                            ⏳ Waiting for {{ $spouse ? $spouse->name : 'spouse' }} to confirm your Nikah declaration.
                        </span>
                        <p class="text-[11px] text-slate-300">
                            A notification has been dispatched to your spouse. Once confirmed, all account transitions will take effect immediately.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Post-Nikah Protections & Status Changes Summary -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white font-heading">
                    Shielded Account Safeguards & End Results
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    How Nikah Connect protects your privacy and concludes your matrimonial journey with dignity.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 flex items-start gap-3">
                    <span class="text-xl">🔒</span>
                    <div>
                        <strong class="text-slate-900 dark:text-white block font-bold">Removed from Discovery</strong>
                        <p class="text-slate-600 dark:text-slate-300 mt-0.5">Both IDs are immediately delisted from search, candidate discovery, and recommendations.</p>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 flex items-start gap-3">
                    <span class="text-xl">🛑</span>
                    <div>
                        <strong class="text-slate-900 dark:text-white block font-bold">Past Inquiries Auto-Closed</strong>
                        <p class="text-slate-600 dark:text-slate-300 mt-0.5">All pending and active inquiries with third suitors have been closed with a polite message of Du'a.</p>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 flex items-start gap-3">
                    <span class="text-xl">💬</span>
                    <div>
                        <strong class="text-slate-900 dark:text-white block font-bold">Chat Channels Archived</strong>
                        <p class="text-slate-600 dark:text-slate-300 mt-0.5">Conversations with other candidates are locked to read-only; your conversation with your spouse remains intact.</p>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 flex items-start gap-3">
                    <span class="text-xl">💳</span>
                    <div>
                        <strong class="text-slate-900 dark:text-white block font-bold">Subscriptions Completed</strong>
                        <p class="text-slate-600 dark:text-slate-300 mt-0.5">Active recurring subscriptions have been gracefully halted with status marked as completed Nikah.</p>
                    </div>
                </div>
            </div>
        </div>

        @if($marriage->isConfirmed())
            <!-- Barakah Story Submission Form -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs space-y-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Community Inspiration</span>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white font-heading mt-0.5">
                        Share Your Barakah Story (Success Story)
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Help inspire practicing brothers and sisters seeking marriage on the Sunnah by sharing your advice and matrimonial journey.
                    </p>
                </div>

                <form action="{{ route('marriages.story', $marriage->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Story Title
                        </label>
                        <input type="text" name="story_title" value="{{ old('story_title', $marriage->story_title ?? 'How We Found Halal Compatibility Through Nikah Connect') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Your Story & Words of Encouragement
                        </label>
                        <textarea name="story_body" rows="4" required placeholder="Share how your values aligned, the role of the Wali, and advice for fellow seekers..."
                                  class="w-full p-4 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">{{ old('story_body', $marriage->story_body) }}</textarea>
                    </div>

                    <div class="flex items-center justify-between flex-wrap gap-4 pt-2">
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="story_is_public" value="1" {{ old('story_is_public', $marriage->story_is_public ?? true) ? 'checked' : '' }}
                                   class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span>Allow publishing anonymously on the Nikah Connect Success Stories page</span>
                        </label>

                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                            Save Barakah Story
                        </button>
                    </div>
                </form>
            </div>
        @endif

    </div>
</div>
@endsection
