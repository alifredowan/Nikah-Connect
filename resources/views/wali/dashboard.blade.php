@extends('layouts.app')

@section('title', 'Wali Guardian Console - Nikah Connect')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Top Welcome Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-800 flex items-center justify-center text-2xl shadow-xs shrink-0">
                    🛡️
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 font-heading">Wali (Guardian) Console</h1>
                    <p class="text-xs text-slate-500 mt-1">Overseeing matrimonial introductions, interest approvals, and chaperoned discussions (FR-4.3, 6.2).</p>
                </div>
            </div>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-purple-100 text-purple-900 border border-purple-200">
                Guardian Oversight Active
            </span>
        </div>

        <!-- Pending Interest Approvals (FR-4.3) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900 font-heading flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Pending Interest Approvals Requiring Your Review
                </h2>
                <span class="text-xs text-slate-500">{{ $pendingInterests->count() }} awaiting review</span>
            </div>

            @forelse($pendingInterests as $pi)
                <div class="bg-white rounded-2xl border-2 border-purple-100 p-6 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl bg-slate-800 overflow-hidden shrink-0">
                            @if($pi->sender->profile?->primaryPhoto)
                                <img src="{{ $pi->sender->profile->primaryPhoto->displayUrl() }}" alt="{{ $pi->sender->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-2xl">
                                    {{ substr($pi->sender->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-purple-700 uppercase tracking-wider block">Introduction Request for: {{ $pi->recipient->name }}</span>
                            <h3 class="font-bold text-base text-slate-900">{{ $pi->sender->name }}</h3>
                            <p class="text-xs text-slate-500">
                                {{ $pi->sender->age }} yrs • {{ $pi->sender->profile?->city }}, {{ $pi->sender->profile?->country }} •
                                <span class="font-semibold text-emerald-800">{{ $pi->sender->profile?->sect_madhhab }}</span>
                            </p>
                            @if($pi->message_note)
                                <p class="text-xs text-slate-600 bg-slate-50 p-2 rounded-lg mt-2 italic">
                                    "{{ $pi->message_note }}"
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                        <a href="{{ route('discovery.show', $pi->sender_id) }}" target="_blank" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            Inspect Profile
                        </a>
                        <form action="{{ route('wali.interests.approve', $pi->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                                ✓ Approve Request
                            </button>
                        </form>
                        <form action="{{ route('wali.interests.reject', $pi->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition">
                                Decline
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-xs text-slate-500">
                    No pending interest requests awaiting your approval right now.
                </div>
            @endforelse
        </div>

        <!-- My Linked Wards -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-slate-900 font-heading">My Linked Wards</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($wards as $wardLink)
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-base">
                                {{ substr($wardLink->seeker->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-900">{{ $wardLink->seeker->name }}</h3>
                                <p class="text-xs text-slate-500">Relationship: <span class="capitalize font-semibold text-slate-700">{{ $wardLink->relationship_type }}</span></p>
                                <span class="text-[10px] text-purple-700 font-bold uppercase">Permission: {{ str_replace('_', ' ', $wardLink->permission_level) }}</span>
                            </div>
                        </div>
                        <a href="{{ route('discovery.show', $wardLink->seeker_user_id) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                            View Ward &rarr;
                        </a>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-8 text-center text-xs text-slate-500">
                        No linked wards currently registered under your email.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Chaperoned Conversations Under My Oversight -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-slate-900 font-heading">Chaperoned Conversations</h2>
            <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 overflow-hidden shadow-xs">
                @forelse($chaperonedConversations as $conv)
                    <a href="{{ route('chat.show', $conv->id) }}" class="p-5 flex items-center justify-between hover:bg-slate-50 transition block">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900">
                                    {{ $conv->participants->pluck('name')->join(' & ') }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                                    Observing as Wali
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Last message: {{ $conv->latestMessage?->body ?? 'Chat active' }}
                            </p>
                        </div>
                        <span class="text-xs font-bold text-emerald-700">Enter Room &rarr;</span>
                    </a>
                @empty
                    <div class="p-8 text-center text-xs text-slate-500">
                        No active chaperoned conversations yet.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
