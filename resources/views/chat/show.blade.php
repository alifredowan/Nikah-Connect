@extends('layouts.app')

@section('title', 'Conversation - Nikah Connect')

@section('content')
<div class="py-6 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-4">

        <!-- Top Navigation & Details -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('chat.index') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-700">
                    &larr; Inbox
                </a>
                <div class="h-4 w-px bg-slate-200"></div>
                <div>
                    <h2 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                        {{ $otherUser?->name ?? 'Candidate' }}
                        @if($otherUser?->is_verified)
                            <span class="text-blue-500 text-xs" title="Verified">✓</span>
                        @endif
                    </h2>
                    <p class="text-[11px] text-slate-500">Halal Matrimonial Communication</p>
                </div>
            </div>

            <!-- Report User Button -->
            <button type="button" onclick="document.getElementById('reportChatModal').classList.remove('hidden')" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                🚩 Report
            </button>
        </div>

        <!-- Chaperone Banner (FR-4.3, 6.2) -->
        @if($waliObserver)
            <div class="bg-purple-50 border border-purple-200 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center text-xl shrink-0">
                    🛡️
                </div>
                <div class="text-xs text-purple-900">
                    <strong class="font-bold block text-purple-950">Islamic Chaperone (Wali) Active</strong>
                    <span>{{ $waliObserver->name }} is present as a guardian observer in this conversation to ensure Islamic marriage etiquette and respectful boundaries (FR-4.3).</span>
                </div>
            </div>
        @endif

        <!-- Message Scanning Reminder Notice (FR-4.4, 6.5) -->
        <div class="bg-amber-50/80 border border-amber-200/80 rounded-xl px-4 py-2 text-[11px] text-amber-800 flex items-center gap-2">
            <span class="text-base">⚠️</span>
            <span><strong>Halal Safety Scanner Active:</strong> Sharing phone numbers, email addresses, or external social media handles (WhatsApp/Instagram/Telegram) is restricted until platform verification (FR-4.4).</span>
        </div>

        <!-- Message Thread Box -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 min-h-[420px] flex flex-col justify-between">
            <div class="space-y-4 mb-6 overflow-y-auto max-h-[500px] pr-2">
                @forelse($conversation->messages as $msg)
                    @php
                        $isMe = $msg->sender_id === Auth::id();
                        $isWali = $msg->sender?->isWali() || ($waliObserver && $msg->sender_id === $waliObserver->id);
                    @endphp

                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                        <div class="flex items-center gap-1 text-[11px] text-slate-400 mb-1 px-1">
                            <span class="font-bold text-slate-700">{{ $msg->sender->name }}</span>
                            @if($isWali)
                                <span class="bg-purple-100 text-purple-800 px-1.5 py-0.2 rounded font-bold text-[9px]">🛡️ Wali Chaperone</span>
                            @endif
                            <span>• {{ $msg->created_at->format('h:i A') }}</span>
                        </div>

                        <div class="max-w-md rounded-2xl px-4 py-3 text-xs leading-relaxed shadow-2xs {{ $isMe ? 'bg-emerald-600 text-white rounded-br-none' : ($isWali ? 'bg-purple-50 border border-purple-200 text-purple-950 rounded-bl-none' : 'bg-slate-100 text-slate-800 rounded-bl-none') }}">
                            <p class="whitespace-pre-line">{{ $msg->body }}</p>
                        </div>

                        @if($msg->is_flagged)
                            <div class="text-[10px] text-amber-700 font-semibold mt-1 flex items-center gap-1">
                                <span>⚠️ Filtered:</span> {{ $msg->flag_reason }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-xs">
                        No messages yet. Send an opening greeting with Islamic etiquette!
                    </div>
                @endforelse
            </div>

            <!-- Send Message Input Form -->
            <form action="{{ route('chat.send', $conversation->id) }}" method="POST" class="pt-4 border-t border-slate-100">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <textarea name="body" rows="2" required
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none resize-none"
                                  placeholder="Type your message with respectful Islamic etiquette..."></textarea>
                    </div>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition shrink-0">
                        Send Message
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- Report Modal -->
<div id="reportChatModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-slate-900 mb-2 font-heading">Report Conversation</h3>
        <form action="{{ route('reports.store') }}" method="POST">
            @csrf
            <input type="hidden" name="reported_id" value="{{ $otherUser?->id }}">
            <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1">Reason</label>
                <select name="reason" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="inappropriate_language">Vulgar / disrespectful language</option>
                    <option value="unsolicited_contact_push">Pushing for external off-platform contact</option>
                    <option value="harassment">Harassment or coercion</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1">Details</label>
                <textarea name="details" rows="3" class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="Explain the issue..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('reportChatModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700">Submit Report</button>
            </div>
        </form>
    </div>
</div>
@endsection
