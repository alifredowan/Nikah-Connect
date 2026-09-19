@extends('layouts.app')

@section('title', 'Halal Conversations - Nikah Connect')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 font-heading">Halal Conversations</h1>
                <p class="text-xs text-slate-500 mt-1">Private communication unlocked after mutual interest and guardian approval (FR-4.2).</p>
            </div>
            <span class="text-xs bg-emerald-50 text-emerald-800 font-semibold px-3 py-1.5 rounded-xl border border-emerald-200">
                🔒 Chaperoned & Protected
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden divide-y divide-slate-100">
            @forelse($conversations as $conversation)
                @php
                    $other = $conversation->getOtherParticipant(Auth::user());
                    $hasWali = $conversation->participants->contains(fn($p) => $p->pivot->role === 'wali_chaperone');
                @endphp
                <a href="{{ route('chat.show', $conversation->id) }}" class="p-5 flex items-center justify-between gap-4 hover:bg-slate-50 transition block">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-800 overflow-hidden shrink-0">
                            @if($other?->profile?->primaryPhoto)
                                <img src="{{ $other->profile->primaryPhoto->displayUrl() }}" alt="{{ $other->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-emerald-950 text-white font-bold text-lg">
                                    {{ substr($other?->name ?? 'User', 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-sm text-slate-900">{{ $other?->name ?? 'Candidate' }}</h3>
                                @if($hasWali)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                                        🛡️ Wali Chaperoned
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 line-clamp-1 mt-0.5">
                                {{ $conversation->latestMessage?->body ?? 'Conversation started...' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[11px] text-slate-400">
                            {{ $conversation->latestMessage?->created_at?->diffForHumans() ?? $conversation->created_at->diffForHumans() }}
                        </span>
                        <span class="block text-xs font-bold text-emerald-700 mt-1">Open &rarr;</span>
                    </div>
                </a>
            @empty
                <div class="p-12 text-center text-slate-500 text-xs">
                    <p class="font-semibold text-sm text-slate-700">No active conversations yet.</p>
                    <p class="mt-1">Messaging unlocks once an Interest request is mutually accepted by both parties.</p>
                    <a href="{{ route('discovery.index') }}" class="inline-block mt-4 px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold">
                        Find Matches
                    </a>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
