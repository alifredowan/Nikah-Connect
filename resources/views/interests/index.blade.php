@extends('layouts.app')

@section('title', 'My Interests - Nikah Connect')

@section('content')
<div class="py-10 bg-slate-50 dark:bg-slate-950 min-h-screen" x-data="{ tab: 'received' }">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white font-heading">Halal Interests & Introductions</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Review expressions of interest sent and received under Islamic etiquette (FR-4.1).</p>
            </div>
            <!-- Tab Buttons -->
            <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                <button @click="tab = 'received'" :class="tab === 'received' ? 'bg-white dark:bg-slate-700 shadow-xs text-emerald-900 dark:text-emerald-200 font-bold' : 'text-slate-600 dark:text-slate-400 font-medium'" class="px-4 py-2 rounded-lg text-xs transition">
                    Received ({{ $received->count() }})
                </button>
                <button @click="tab = 'sent'" :class="tab === 'sent' ? 'bg-white dark:bg-slate-700 shadow-xs text-emerald-900 dark:text-emerald-200 font-bold' : 'text-slate-600 dark:text-slate-400 font-medium'" class="px-4 py-2 rounded-lg text-xs transition">
                    Sent ({{ $sent->count() }})
                </button>
            </div>
        </div>

        <!-- Received Tab -->
        <div x-show="tab === 'received'" class="space-y-4">
            @forelse($received as $interest)
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-800 shrink-0">
                            @if($interest->sender->profile?->primaryPhoto)
                                <img src="{{ $interest->sender->profile->primaryPhoto->displayUrl() }}" alt="{{ $interest->sender->name }}" class="w-full h-full object-cover photo-blur">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-xl">
                                    {{ substr($interest->sender->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $interest->sender->name }}</h3>
                                @if($interest->sender->is_verified)
                                    <span class="bg-blue-600 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full">✓ Verified</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $interest->sender->age }} yrs • {{ $interest->sender->profile?->city }}, {{ $interest->sender->profile?->country }} •
                                <span class="text-emerald-700 dark:text-emerald-400 font-medium">{{ $interest->sender->profile?->sect_madhhab }}</span>
                            </p>
                            @if($interest->message_note)
                                <p class="text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-2 rounded-lg mt-2 italic">
                                    "{{ $interest->message_note }}"
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        @if($interest->status === 'pending')
                            <form action="{{ route('interests.respond', $interest->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="accept">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                                    Accept Interest
                                </button>
                            </form>
                            <form action="{{ route('interests.respond', $interest->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="decline">
                                <button type="submit" class="px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold transition">
                                    Decline
                                </button>
                            </form>
                        @elseif($interest->status === 'accepted')
                            <div class="text-right">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">
                                    Accepted
                                </span>
                                @if($interest->conversation)
                                    <a href="{{ route('chat.show', $interest->conversation->id) }}" class="block text-xs text-emerald-700 dark:text-emerald-400 font-bold hover:underline mt-1">
                                        Open Chat &rarr;
                                    </a>
                                @endif
                            </div>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 capitalize">
                                {{ $interest->status }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 p-12 rounded-2xl border border-slate-200 dark:border-slate-800 text-center">
                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No interest requests received yet.</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Complete your profile to increase discovery visibility!</p>
                </div>
            @endforelse
        </div>

        <!-- Sent Tab -->
        <div x-show="tab === 'sent'" x-cloak class="space-y-4">
            @forelse($sent as $interest)
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-800 shrink-0">
                            @if($interest->recipient->profile?->primaryPhoto)
                                <img src="{{ $interest->recipient->profile->primaryPhoto->displayUrl() }}" alt="{{ $interest->recipient->name }}" class="w-full h-full object-cover photo-blur">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-xl">
                                    {{ substr($interest->recipient->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $interest->recipient->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $interest->recipient->age }} yrs • {{ $interest->recipient->profile?->city }}, {{ $interest->recipient->profile?->country }}
                            </p>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500 block mt-1">Sent on {{ $interest->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($interest->status === 'accepted')
                            @if($interest->wali_approval_status === 'pending')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 dark:bg-purple-950/80 text-purple-800 dark:text-purple-300">
                                    🛡️ Awaiting Wali Approval
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">
                                    ✓ Accepted & Unlocked
                                </span>
                                @if($interest->conversation)
                                    <a href="{{ route('chat.show', $interest->conversation->id) }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                                        Open Chat
                                    </a>
                                @endif
                            @endif
                        @elseif($interest->status === 'pending')
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300">
                                ⏳ Pending Recipient Response
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 capitalize">
                                {{ $interest->status }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 p-12 rounded-2xl border border-slate-200 dark:border-slate-800 text-center">
                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">You haven't sent any interest requests yet.</p>
                    <a href="{{ route('discovery.index') }}" class="inline-block mt-3 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold">
                        Browse Candidates
                    </a>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
