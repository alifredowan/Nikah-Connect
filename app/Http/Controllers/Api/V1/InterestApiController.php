<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Models\User;
use App\Services\QuotaService;
use App\Services\WaliWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterestApiController extends Controller
{
    public function __construct(
        protected QuotaService $quotaService,
        protected WaliWorkflowService $waliService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $received = Interest::with('sender.profile.primaryPhoto')
            ->where('recipient_id', $user->id)
            ->latest()
            ->get();

        $sent = Interest::with('recipient.profile.primaryPhoto')
            ->where('sender_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'received' => $received,
                'sent' => $sent,
            ],
        ]);
    }

    public function store(Request $request, int $recipientId): JsonResponse
    {
        $user = $request->user();

        if ($user->id === $recipientId) {
            return response()->json(['status' => 'error', 'message' => 'Cannot send interest to yourself'], 422);
        }

        if (! $this->quotaService->canSendInterest($user)) {
            return response()->json(['status' => 'error', 'message' => 'Daily interest quota exceeded'], 429);
        }

        $recipient = User::findOrFail($recipientId);
        $requiresWali = $this->waliService->requiresWaliApproval($recipient);

        $interest = Interest::firstOrCreate([
            'sender_id' => $user->id,
            'recipient_id' => $recipient->id,
        ], [
            'status' => 'pending',
            'wali_approval_status' => $requiresWali ? 'pending' : 'not_required',
            'message_note' => $request->note,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Interest sent successfully',
            'data' => $interest,
        ], 201);
    }

    public function respond(Request $request, int $interestId): JsonResponse
    {
        $request->validate(['action' => 'required|in:accept,decline,ignore']);
        $user = $request->user();

        $interest = Interest::where('recipient_id', $user->id)->findOrFail($interestId);

        if ($request->action === 'accept') {
            $interest->update(['status' => 'accepted', 'responded_at' => now()]);
            $unlocked = false;
            if ($interest->wali_approval_status !== 'pending') {
                $this->waliService->createOrUnlockConversation($interest);
                $unlocked = true;
            }

            return response()->json([
                'status' => 'success',
                'message' => $unlocked ? 'Interest accepted and chat unlocked' : 'Interest accepted, awaiting wali approval',
                'chat_unlocked' => $unlocked,
            ]);
        }

        $interest->update(['status' => $request->action, 'responded_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Interest updated',
        ]);
    }
}
