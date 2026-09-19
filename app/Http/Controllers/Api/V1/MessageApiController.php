<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\MessageModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageApiController extends Controller
{
    public function __construct(
        protected MessageModerationService $moderationService
    ) {}

    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::whereHas('participants', function ($q) use ($user) {
            $q->where('conversation_participants.user_id', $user->id);
        })->with(['participants', 'latestMessage'])->latest('updated_at')->get();

        return response()->json([
            'status' => 'success',
            'data' => $conversations,
        ]);
    }

    public function messages(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::with(['messages.sender', 'participants'])
            ->findOrFail($conversationId);

        if (! $conversation->participants->contains('id', $user->id)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $conversation->messages,
        ]);
    }

    public function send(Request $request, int $conversationId): JsonResponse
    {
        $request->validate(['body' => 'required|string|max:2000']);
        $user = $request->user();

        $conversation = Conversation::findOrFail($conversationId);
        if ($conversation->isLocked()) {
            return response()->json(['status' => 'error', 'message' => 'Conversation is locked'], 422);
        }

        $scan = $this->moderationService->scan($request->body);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $scan['sanitized_body'],
            'is_flagged' => $scan['is_flagged'],
            'flag_reason' => $scan['flag_reason'],
        ]);

        $conversation->touch();

        return response()->json([
            'status' => 'success',
            'data' => $message,
            'safety_warning' => $scan['is_flagged'] ? $scan['flag_reason'] : null,
        ], 201);
    }
}
