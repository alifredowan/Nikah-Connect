<?php

namespace App\Http\Controllers;

use App\Events\MessageSentEvent;
use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\Report;
use App\Models\WaliLink;
use App\Notifications\NewChatMessageNotification;
use App\Services\MessageModerationService;
use App\Services\WaliWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        protected MessageModerationService $moderationService,
        protected WaliWorkflowService $waliService
    ) {}

    public function index(): View
    {
        $user = Auth::user();

        // If current user is a Wali, ensure they are synced as chaperone to all of their wards' conversations
        $wardIds = WaliLink::where(function ($q) use ($user) {
            $q->where('wali_user_id', $user->id)
                ->orWhere('wali_email', $user->email);
        })
            ->where('status', 'active')
            ->pluck('seeker_user_id');

        if ($wardIds->isNotEmpty()) {
            $wardConversations = Conversation::whereHas('participants', function ($q) use ($wardIds) {
                $q->whereIn('conversation_participants.user_id', $wardIds);
            })->get();

            foreach ($wardConversations as $wc) {
                ConversationParticipant::firstOrCreate([
                    'conversation_id' => $wc->id,
                    'user_id' => $user->id,
                ], [
                    'role' => 'wali_chaperone',
                ]);
            }
        }

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

        // Ensure active Walis of all seekers in this conversation are added as chaperone participants
        $this->waliService->ensureWaliParticipants($conversation);
        $conversation->load('participants.profile.primaryPhoto');

        // Security check: must be a direct participant, active Wali of a participant, or admin/mod
        $isParticipant = $conversation->participants->contains('id', $user->id);
        if (! $isParticipant) {
            $participantIds = $conversation->participants->pluck('id');
            $isWaliOfParticipant = WaliLink::whereIn('seeker_user_id', $participantIds)
                ->where(function ($q) use ($user) {
                    $q->where('wali_user_id', $user->id)
                        ->orWhere('wali_email', $user->email);
                })
                ->where('status', 'active')
                ->exists();

            if ($isWaliOfParticipant) {
                ConversationParticipant::firstOrCreate([
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                ], [
                    'role' => 'wali_chaperone',
                ]);
                $conversation->load('participants.profile.primaryPhoto');
                $isParticipant = true;
            }
        }

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

    public function sendMessage(Request $request, int $conversationId): RedirectResponse|JsonResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        $conversation = Conversation::with('participants')->findOrFail($conversationId);

        // Ensure active Walis are attached as chaperone participants
        $this->waliService->ensureWaliParticipants($conversation);
        $conversation->load('participants');

        // Security check: must be a participant, authorized wali, or admin
        $isParticipant = $conversation->participants->contains('id', $user->id);
        if (! $isParticipant) {
            $participantIds = $conversation->participants->pluck('id');
            $isWaliOfParticipant = WaliLink::whereIn('seeker_user_id', $participantIds)
                ->where(function ($q) use ($user) {
                    $q->where('wali_user_id', $user->id)
                        ->orWhere('wali_email', $user->email);
                })
                ->where('status', 'active')
                ->exists();

            if ($isWaliOfParticipant) {
                ConversationParticipant::firstOrCreate([
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                ], [
                    'role' => 'wali_chaperone',
                ]);
                $conversation->load('participants');
                $isParticipant = true;
            }
        }

        if (! $isParticipant && ! $user->isAdmin() && ! $user->isModerator()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'You are not a participant in this conversation.'], 403);
            }

            return redirect()->route('chat.index')->with('error', 'You are not a participant in this conversation.');
        }

        // If Wali has view_only permission, prevent them from sending
        $myPivot = $conversation->participants->firstWhere('id', $user->id)?->pivot;
        if ($myPivot && $myPivot->role === 'wali_chaperone') {
            $waliLink = WaliLink::where('wali_user_id', $user->id)
                ->whereIn('seeker_user_id', $conversation->participants->pluck('id'))
                ->where('permission_level', 'view_only')
                ->first();

            if ($waliLink) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => 'Your guardian permission is set to View Only.'], 403);
                }

                return back()->with('error', 'Your guardian permission is set to View Only.');
            }
        }

        if ($conversation->isLocked()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'This conversation has been locked.'], 403);
            }

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

        // 1. Broadcast MessageSentEvent over Reverb WebSocket (safeguarded)
        try {
            broadcast(new MessageSentEvent($message))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Reverb message broadcast failed: '.$e->getMessage());
        }

        // 2. Dispatch notifications to all other participants (seekers & chaperone walis)
        foreach ($conversation->participants as $participant) {
            if ($participant->id !== $user->id) {
                try {
                    $isChaperone = ($participant->pivot->role === 'wali_chaperone');
                    $participant->notify(new NewChatMessageNotification(
                        message: $message,
                        targetUserId: $participant->id,
                        isWali: $isChaperone
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Chat message notification failed: '.$e->getMessage());
                }
            }
        }

        if ($scanResult['is_flagged']) {
            AuditLog::record($user->id, 'message_flagged', 'Message', $message->id, [
                'reason' => $scanResult['flag_reason'],
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'is_flagged' => true,
                    'flag_reason' => $scanResult['flag_reason'],
                    'message' => [
                        'id' => $message->id,
                        'sender_id' => $message->sender_id,
                        'sender_name' => $user->name,
                        'body' => $message->body,
                        'created_at' => $message->created_at->format('g:i A'),
                    ],
                ]);
            }

            return back()->with('warning', 'Notice: Nikah Connect privacy filters detected potential contact information or prohibited keywords ('.$scanResult['flag_reason'].'). Sharing external contacts before platform verification is restricted (FR-4.4).');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_flagged' => false,
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $user->name,
                    'body' => $message->body,
                    'created_at' => $message->created_at->format('g:i A'),
                ],
            ]);
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
