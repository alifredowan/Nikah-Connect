@extends('layouts.app')

@section('title', 'Notifications - Nikah Connect')

@section('content')
<div class="py-8 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold font-heading text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🔔</span> Notifications
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Stay updated with real-time interest requests, guardian updates, and halal chat messages.
                </p>
            </div>

            @if(Auth::user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800 hover:text-emerald-700 transition shadow-2xs">
                        ✓ Mark All as Read
                    </button>
                </form>
            @endif
        </div>

        <!-- Notification List -->
        <div class="space-y-3">
            @forelse($notifications as $n)
                @php
                    $data = $n->data;
                    $isUnread = is_null($n->read_at);
                    $icon = $data['icon'] ?? '🔔';
                    $type = $data['type'] ?? 'general';
                    $candidateUserId = $data['sender_id'] ?? $data['recipient_id'] ?? null;
                    $candidateProfileUrl = $candidateUserId ? route('discovery.show', $candidateUserId) : null;
                    $actionUrl = !empty($data['action_url']) ? $data['action_url'] : $candidateProfileUrl;
                    if ($type === 'interest_received' && $candidateProfileUrl) {
                        $actionUrl = $candidateProfileUrl;
                    }
                    $currentInterest = !empty($data['interest_id']) && isset($interests[$data['interest_id']]) ? $interests[$data['interest_id']] : null;
                    $interestStatus = $currentInterest ? $currentInterest->status : null;
                @endphp

                <div class="p-4 sm:p-5 rounded-2xl border transition duration-150 {{ $isUnread ? 'bg-white dark:bg-slate-900 border-emerald-300 dark:border-emerald-800/70 shadow-sm' : 'bg-white/70 dark:bg-slate-900/60 border-slate-200 dark:border-slate-800/80 opacity-90' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3.5 flex-1 min-w-0">
                            @if($candidateProfileUrl)
                                <a href="{{ $candidateProfileUrl }}" class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-xl shrink-0 hover:scale-105 hover:ring-2 hover:ring-emerald-500/40 transition shadow-2xs cursor-pointer" title="View Candidate Profile">
                                    {{ $icon }}
                                </a>
                            @else
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xl shrink-0">
                                    {{ $icon }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ $data['title'] ?? 'Notification' }}
                                    </h3>
                                    @if($isUnread)
                                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                    @if($type === 'interest_received' && !empty($data['sender_name']) && $candidateProfileUrl)
                                        <a href="{{ $candidateProfileUrl }}" class="font-bold text-emerald-700 dark:text-emerald-400 hover:underline">
                                            {{ $data['sender_name'] }}
                                        </a>
                                        has sent you an expression of interest!
                                    @else
                                        {{ $data['message'] ?? '' }}
                                    @endif
                                </p>

                                @if(!empty($data['note']))
                                    <div class="mt-2 text-xs bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 italic">
                                        &ldquo;{{ $data['note'] }}&rdquo;
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2.5 mt-3 flex-wrap">
                                    @if($candidateProfileUrl)
                                        <a href="{{ $candidateProfileUrl }}" class="px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 font-bold text-xs border border-emerald-300 dark:border-emerald-800 transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                            <span>👤</span> View Profile & Biodata
                                        </a>
                                    @endif

                                    @if($type === 'interest_received' && !empty($data['interest_id']))
                                        @if($interestStatus === 'accepted')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 text-xs font-bold border border-emerald-300 dark:border-emerald-800">
                                                <span>✓</span> Interest Accepted
                                            </span>
                                            @if($currentInterest && $currentInterest->conversation)
                                                <a href="{{ route('chat.show', $currentInterest->conversation->id) }}" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline inline-flex items-center gap-1">
                                                    <span>💬 Open Messages &rarr;</span>
                                                </a>
                                            @endif
                                        @elseif($interestStatus === 'declined')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold capitalize border border-slate-200 dark:border-slate-700">
                                                <span>✕</span> Declined
                                            </span>
                                        @else
                                            <!-- Direct Accept & Decline Buttons -->
                                            <form action="{{ route('interests.respond', $data['interest_id']) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition cursor-pointer">
                                                    ✓ Accept Interest
                                                </button>
                                            </form>

                                            <form action="{{ route('interests.respond', $data['interest_id']) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="decline">
                                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition border border-slate-200 dark:border-slate-700 cursor-pointer">
                                                    ✕ Decline
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    @if(!empty($actionUrl) && $actionUrl !== $candidateProfileUrl)
                                        <a href="{{ $actionUrl }}" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline">
                                            View Details &rarr;
                                        </a>
                                    @endif

                                    @if($isUnread)
                                        <form action="{{ route('notifications.read', $n->id) }}" method="POST" class="inline ml-auto">
                                            @csrf
                                            <button type="submit" class="text-[11px] text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer">
                                                Mark as read
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <span class="text-[11px] text-slate-400 dark:text-slate-500 whitespace-nowrap shrink-0">
                            {{ $n->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 shadow-xs">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mb-3">
                        🔔
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">No notifications yet</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                        When candidate seekers express interest, guardians approve requests, or new messages arrive, you will receive real-time notifications here.
                    </p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
