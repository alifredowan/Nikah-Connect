<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\Report;
use App\Services\MessageModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        protected MessageModerationService $moderationService
    ) {}

    public function index(): View
    {
        $user = Auth::user();

        $conversations = Conversation::whereHas('participants', function ($q) use ($user) {
            $q->where('conversation_participants.user_id', $user->id);
        })->with(['participants.profile.primaryPhoto', 'latestMessage', 'interest.recipient.profile', 'interest.sender.profile'])
            ->latest('updated_at')
            ->get();

        return view('chat.index', compact('conversations'));
    }

    public function show(int $conversationId): View|RedirectResponse
    {
        $user = Auth::user();

        $conversation = Conversation::with([
            'participants.profile.primaryPhoto',
            'messages.sender',
            'interest',
        ])->findOrFail($conversationId);

        // Security check: must be a participant or admin
        $isParticipant = $conversation->participants->contains('id', $user->id);
        if (! $isParticipant && ! $user->isAdmin() && ! $user->isModerator()) {
            return redirect()->route('chat.index')->with('error', 'You are not a participant in this conversation.');
        }

        // Mark messages as read for this participant
        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        // Check if there is a Wali chaperone observer in this conversation
        $waliObserver = $conversation->participants->firstWhere('pivot.role', 'wali_chaperone');
        $otherUser = $conversation->getOtherParticipant($user);

        return view('chat.show', compact('conversation', 'otherUser', 'waliObserver'));
    }

    public function sendMessage(Request $request, int $conversationId): RedirectResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->isLocked()) {
            return back()->with('error', 'This conversation has been locked.');
        }

        // Run through safety and contact scanning (FR-4.4, 6.5)
        $scanResult = $this->moderationService->scan($request->body);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $scanResult['sanitized_body'],
            'is_flagged' => $scanResult['is_flagged'],
            'flag_reason' => $scanResult['flag_reason'],
        ]);

        $conversation->touch();

        if ($scanResult['is_flagged']) {
            AuditLog::record($user->id, 'message_flagged', 'Message', $message->id, [
                'reason' => $scanResult['flag_reason'],
            ]);

            return back()->with('warning', 'Notice: Nikah Connect privacy filters detected potential contact information or prohibited keywords ('.$scanResult['flag_reason'].'). Sharing external contacts before platform verification is restricted (FR-4.4).');
        }

        return back();
    }

    public function reportUser(Request $request): RedirectResponse
    {
        $request->validate([
            'reported_id' => ['required', 'exists:users,id'],
            'reason' => ['required', 'string', 'max:100'],
            'details' => ['nullable', 'string', 'max:1000'],
            'conversation_id' => ['nullable', 'exists:conversations,id'],
        ]);

        $user = Auth::user();

        Report::create([
            'reporter_id' => $user->id,
            'reported_id' => $request->reported_id,
            'conversation_id' => $request->conversation_id,
            'reason' => $request->reason,
            'details' => $request->details,
            'status' => 'pending',
        ]);

        AuditLog::record($user->id, 'user_reported', 'User', $request->reported_id, [
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'User report submitted to platform moderation team (FR-4.6, 7.2). We will review this report within 24 hours.');
    }
}
