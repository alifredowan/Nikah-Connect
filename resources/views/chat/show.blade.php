@extends('layouts.app')

@section('title', 'Conversation - Nikah Connect')

@section('content')
<div class="py-6 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-4">

        <!-- Top Navigation & Details -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('chat.index') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400">
                    &larr; Inbox
                </a>
                <div class="h-4 w-px bg-slate-200 dark:bg-slate-800"></div>
                <div>
                    <h2 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                        @if($waliObserver && Auth::id() === $waliObserver->id)
                            {{ $conversation->participants->where('pivot.role', 'seeker')->pluck('name')->join(' & ') }}
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/70 text-purple-800 dark:text-purple-300 font-bold border border-purple-200 dark:border-purple-800/60">Observing as Wali</span>
                        @else
                            {{ $otherUser?->name ?? 'Candidate' }}
                            @if($otherUser?->is_verified)
                                <span class="text-blue-500 text-xs" title="Verified">✓</span>
                            @endif
                            @if($waliObserver)
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/70 text-purple-800 dark:text-purple-300 font-bold border border-purple-200 dark:border-purple-800/60 flex items-center gap-1">
                                    🛡️ Wali: {{ $waliObserver->name }}
                                </span>
                            @endif
                        @endif
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Halal Matrimonial Communication</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                @if($marriage)
                    <a href="{{ route('marriages.celebration') }}" class="px-3 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-400/50 text-amber-800 dark:text-amber-300 text-xs font-bold flex items-center gap-1 transition">
                        <span>💍</span>
                        @if($marriage->isConfirmed())
                            <span>Nikah Mubarak ↗</span>
                        @else
                            <span>Nikah Pending ↗</span>
                        @endif
                    </a>
                @elseif($otherUser && !Auth::user()->isMarried() && !$otherUser->isMarried() && $conversation->status !== 'locked' && (!$waliObserver || Auth::id() !== $waliObserver->id))
                    <button type="button" onclick="document.getElementById('declareNikahModal').classList.remove('hidden')" class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/70 hover:bg-emerald-100 dark:hover:bg-emerald-900/70 border border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200 text-xs font-bold flex items-center gap-1 transition cursor-pointer">
                        <span>💍</span> Declare Nikah
                    </button>
                @endif

                <!-- Report User Button -->
                <button type="button" onclick="document.getElementById('reportChatModal').classList.remove('hidden')" class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-300">
                    🚩 Report
                </button>
            </div>
        </div>

        <!-- Chaperone Banner (FR-4.3, 6.2) -->
        @if($waliObserver)
            <div class="bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-900/60 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/60 text-purple-800 dark:text-purple-300 flex items-center justify-center text-xl shrink-0">
                    🛡️
                </div>
                <div class="text-xs text-purple-900 dark:text-purple-300">
                    <strong class="font-bold block text-purple-950 dark:text-purple-200">Islamic Chaperone (Wali) Active</strong>
                    <span>{{ $waliObserver->name }} is present as a guardian observer in this conversation to ensure Islamic marriage etiquette and respectful boundaries (FR-4.3).</span>
                </div>
            </div>
        @endif

        <!-- Message Scanning Reminder Notice (FR-4.4, 6.5) -->
        <div class="bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-900/60 rounded-xl px-4 py-2 text-[11px] text-amber-800 dark:text-amber-300 flex items-center gap-2">
            <span class="text-base">⚠️</span>
            <span><strong>Halal Safety Scanner Active:</strong> Sharing phone numbers, email addresses, or external social media handles (WhatsApp/Instagram/Telegram) is restricted until platform verification (FR-4.4).</span>
        </div>

        <!-- Message Thread Box -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 min-h-[420px] flex flex-col justify-between">
            <div id="chat-messages-container" class="space-y-4 mb-6 overflow-y-auto max-h-[500px] pr-2 scroll-smooth">
                @forelse($conversation->messages as $msg)
                    @php
                        $isMe = $msg->sender_id === Auth::id();
                        $isWali = $msg->sender?->isWali() || ($waliObserver && $msg->sender_id === $waliObserver->id);
                    @endphp

                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}" data-message-id="{{ $msg->id }}">
                        <div class="flex items-center gap-1 text-[11px] text-slate-400 dark:text-slate-500 mb-1 px-1">
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $msg->sender->name }}</span>
                            @if($isWali)
                                <span class="bg-purple-100 dark:bg-purple-950/70 text-purple-800 dark:text-purple-300 px-1.5 py-0.2 rounded font-bold text-[9px] border border-purple-200 dark:border-purple-800/60">🛡️ Wali Chaperone</span>
                            @endif
                            <span>• {{ $msg->created_at->format('h:i A') }}</span>
                        </div>

                        <div class="max-w-md rounded-2xl px-4 py-3 text-xs leading-relaxed shadow-2xs {{ $isMe ? 'bg-emerald-600 text-white rounded-br-none' : ($isWali ? 'bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800 text-purple-950 dark:text-purple-200 rounded-bl-none' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-bl-none') }}">
                            <p class="whitespace-pre-line">{{ $msg->body }}</p>
                        </div>

                        @if($msg->is_flagged)
                            <div class="text-[10px] text-amber-700 dark:text-amber-400 font-semibold mt-1 flex items-center gap-1">
                                <span>⚠️ Filtered:</span> {{ $msg->flag_reason }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div id="chat-empty-state" class="py-12 text-center text-slate-400 dark:text-slate-500 text-xs">
                        No messages yet. Send an opening greeting with Islamic etiquette!
                    </div>
                @endforelse
            </div>

            @php
                $myPivot = $conversation->participants->firstWhere('id', Auth::id())?->pivot;
                $isViewOnlyWali = false;
                if ($myPivot && $myPivot->role === 'wali_chaperone') {
                    $isViewOnlyWali = \App\Models\WaliLink::where('wali_user_id', Auth::id())
                        ->whereIn('seeker_user_id', $conversation->participants->pluck('id'))
                        ->where('permission_level', 'view_only')
                        ->exists();
                }
            @endphp

            @if($conversation->status === 'locked')
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-center py-3 px-4 text-xs text-slate-600 dark:text-slate-300 bg-slate-100/70 dark:bg-slate-800/60 rounded-xl flex items-center justify-center gap-2">
                    <span>🔒</span>
                    <span>This conversation is locked ({{ $conversation->locked_reason === 'candidate_married' ? 'Participant completed Nikah milestone' : 'Archived' }}). Messages are read-only.</span>
                </div>
            @elseif($isViewOnlyWali)
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-center py-3 text-xs text-purple-700 dark:text-purple-300 bg-purple-50/50 dark:bg-purple-950/20 rounded-xl">
                    🛡️ You are observing this conversation with <strong>View Only</strong> guardian permissions.
                </div>
            @else
                <!-- Send Message Input Form -->
                <form id="chat-send-form" action="{{ route('chat.send', $conversation->id) }}" method="POST" class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    @csrf
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <textarea id="chat-message-input" name="body" rows="2" required
                                      class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 text-xs focus:ring-2 focus:ring-emerald-500 outline-none resize-none"
                                      placeholder="Type your message with respectful Islamic etiquette (Press Enter to send)..."></textarea>
                        </div>
                        <button type="submit" id="chat-submit-btn" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition shrink-0 flex items-center gap-1">
                            <span>Send</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
                </form>
            @endif
        </div>

    </div>
</div>

<!-- Report Modal -->
<div id="reportChatModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 font-heading">Report Conversation</h3>
        <form action="{{ route('reports.store') }}" method="POST">
            @csrf
            <input type="hidden" name="reported_id" value="{{ $otherUser?->id }}">
            <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Reason</label>
                <select name="reason" required class="w-full p-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="inappropriate_language">Vulgar / disrespectful language</option>
                    <option value="unsolicited_contact_push">Pushing for external off-platform contact</option>
                    <option value="harassment">Harassment or coercion</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Details</label>
                <textarea name="details" rows="3" class="w-full p-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 text-xs focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="Explain the issue..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('reportChatModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700">Submit Report</button>
            </div>
        </form>
    </div>
</div>

@if($otherUser && !Auth::user()->isMarried() && !$otherUser->isMarried() && $conversation->status !== 'locked')
<!-- Declare Nikah Completion Modal -->
<div id="declareNikahModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-5">
        <div class="flex items-start justify-between">
            <div>
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400">Sacred Milestone</span>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading mt-0.5">Declare Nikah Milestone</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Declare that you and {{ $otherUser->name }} have completed your Nikah under the Sunnah of Allah's Messenger ﷺ.
                </p>
            </div>
            <button type="button" onclick="document.getElementById('declareNikahModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">✕</button>
        </div>

        <form action="{{ route('marriages.initiate') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="spouse_id" value="{{ $otherUser->id }}">

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nikah Date</label>
                <input type="date" name="marriage_date" value="{{ date('Y-m-d') }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Personal Note / Details (Optional)</label>
                <textarea name="confirmation_notes" rows="3" placeholder="Alhamdulillah our families agreed and Nikah was held with Islamic etiquette..."
                          class="w-full p-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
            </div>

            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 text-[11px] text-amber-800 dark:text-amber-200">
                ⚠️ Once submitted, a confirmation prompt will be sent to <strong>{{ $otherUser->name }}</strong>. Upon their confirmation, both accounts will be transitioned to Married, inquiries closed, and privacy shielded.
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('declareNikahModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-extrabold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition cursor-pointer">
                    💍 Submit Nikah Declaration
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('chat-messages-container');
    const form = document.getElementById('chat-send-form');
    const input = document.getElementById('chat-message-input');
    const emptyState = document.getElementById('chat-empty-state');
    const currentUserId = {{ Auth::id() }};
    const conversationId = {{ $conversation->id }};
    const waliObserverId = {{ $waliObserver ? $waliObserver->id : 'null' }};

    // Track already rendered message IDs to guarantee zero duplicates
    const renderedMessageIds = new Set();
    if (container) {
        container.querySelectorAll('[data-message-id]').forEach(el => {
            const id = el.getAttribute('data-message-id');
            if (id) renderedMessageIds.add(String(id));
        });
    }

    function scrollToBottom() {
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    scrollToBottom();

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Append a message bubble to the chat container
    function appendMessageBubble(msg, isMe, isWali) {
        if (!msg) return;
        const msgId = msg.id ? String(msg.id) : null;

        // Check if message with this ID already rendered
        if (msgId && renderedMessageIds.has(msgId)) {
            return;
        }
        if (msgId && container && container.querySelector(`[data-message-id="${msgId}"]`)) {
            renderedMessageIds.add(msgId);
            return;
        }
        if (msgId) {
            renderedMessageIds.add(msgId);
        }

        if (emptyState && emptyState.parentElement) {
            emptyState.remove();
        }

        const wrapper = document.createElement('div');
        wrapper.className = `flex flex-col ${isMe ? 'items-end' : 'items-start'} animate-in fade-in slide-in-from-bottom-2`;
        if (msgId) wrapper.setAttribute('data-message-id', msgId);

        const waliBadge = isWali ? '<span class="bg-purple-100 dark:bg-purple-950/70 text-purple-800 dark:text-purple-300 px-1.5 py-0.2 rounded font-bold text-[9px] border border-purple-200 dark:border-purple-800/60">🛡️ Wali Chaperone</span>' : '';
        const bubbleStyle = isMe 
            ? 'bg-emerald-600 text-white rounded-br-none' 
            : (isWali ? 'bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800 text-purple-950 dark:text-purple-200 rounded-bl-none' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-bl-none');

        const flaggedNotice = msg.is_flagged 
            ? `<div class="text-[10px] text-amber-700 dark:text-amber-400 font-semibold mt-1 flex items-center gap-1"><span>⚠️ Filtered:</span> ${escapeHtml(msg.flag_reason || 'Sensitive info')}</div>` 
            : '';

        wrapper.innerHTML = `
            <div class="flex items-center gap-1 text-[11px] text-slate-400 dark:text-slate-500 mb-1 px-1">
                <span class="font-bold text-slate-700 dark:text-slate-300">${escapeHtml(msg.sender_name || 'User')}</span>
                ${waliBadge}
                <span>• ${escapeHtml(msg.created_at || 'Just now')}</span>
            </div>
            <div class="max-w-md rounded-2xl px-4 py-3 text-xs leading-relaxed shadow-2xs ${bubbleStyle}">
                <p class="whitespace-pre-line">${escapeHtml(msg.body)}</p>
            </div>
            ${flaggedNotice}
        `;

        container.appendChild(wrapper);
        scrollToBottom();
    }

    // Handle AJAX message submit
    const submitBtn = document.getElementById('chat-submit-btn');

    if (form && input) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            }

            const socketId = (window.Echo && typeof window.Echo.socketId === 'function') ? window.Echo.socketId() : null;
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (socketId) {
                headers['X-Socket-ID'] = socketId;
            }

            fetch(form.action, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ body: text })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(errData => {
                        throw new Error(errData.error || errData.message || 'Server returned error ' + res.status);
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.success && data.message) {
                    input.value = '';
                    appendMessageBubble(data.message, true, false);
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(err => {
                console.error('Send message failed:', err);
                alert('Could not send message: ' + (err.message || 'Please check your connection and try again.'));
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                }
                input.focus();
            });
        });

        // Submit on Enter without Shift
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit', { cancelable: true }));
            }
        });
    }

    // Reverb WebSocket listener for live chat messages
    if (window.Echo) {
        const handleIncoming = function(e) {
            if (!e) return;
            // Only render if sent by another participant
            if (parseInt(e.sender_id) !== currentUserId) {
                const isWali = Boolean(e.is_wali) || (waliObserverId !== null && parseInt(e.sender_id) === waliObserverId);
                appendMessageBubble(e, false, isWali);
            }
        };

        window.Echo.private('conversation.' + conversationId)
            .listen('.MessageSentEvent', handleIncoming);
    }
});
</script>
@endsection
