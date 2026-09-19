<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\Interest;
use App\Models\User;
use App\Services\QuotaService;
use App\Services\WaliWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InterestController extends Controller
{
    public function __construct(
        protected QuotaService $quotaService,
        protected WaliWorkflowService $waliService
    ) {}

    public function index(): View
    {
        $user = Auth::user();

        $received = Interest::with(['sender.profile.primaryPhoto'])
            ->where('recipient_id', $user->id)
            ->latest()
            ->get();

        $sent = Interest::with(['recipient.profile.primaryPhoto'])
            ->where('sender_id', $user->id)
            ->latest()
            ->get();

        return view('interests.index', compact('received', 'sent'));
    }

    public function send(Request $request, int $recipientId): RedirectResponse
    {
        $sender = Auth::user();

        if ($sender->id === $recipientId) {
            return back()->with('error', 'You cannot send an interest request to yourself.');
        }

        $recipient = User::with('profile')->findOrFail($recipientId);

        // Enforce daily quota (FR-5.1)
        if (! $this->quotaService->canSendInterest($sender)) {
            return redirect()->route('subscription.pricing')->with('warning', 'You have reached your daily limit of 5 interest requests. Upgrade to Premium for unlimited requests!');
        }

        // Check if interest already exists
        $existing = Interest::where('sender_id', $sender->id)
            ->where('recipient_id', $recipient->id)
            ->first();

        if ($existing) {
            return back()->with('info', 'You have already sent an interest request to this candidate.');
        }

        $requiresWali = $this->waliService->requiresWaliApproval($recipient);

        $interest = Interest::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => 'pending',
            'wali_approval_status' => $requiresWali ? 'pending' : 'not_required',
            'message_note' => $request->input('note'),
        ]);

        AuditLog::record($sender->id, 'interest_sent', 'Interest', $interest->id, ['recipient_id' => $recipient->id]);

        $message = $requiresWali
            ? 'Interest request sent! Because this candidate has a guardian linked, communication will be chaperoned according to Islamic protocol (FR-4.3).'
            : 'Interest request sent successfully!';

        return back()->with('success', $message);
    }

    public function respond(Request $request, int $interestId): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:accept,decline,ignore'],
        ]);

        $user = Auth::user();
        $interest = Interest::where('recipient_id', $user->id)->findOrFail($interestId);

        $action = $request->action;

        if ($action === 'accept') {
            $interest->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            AuditLog::record($user->id, 'interest_accepted', 'Interest', $interest->id);

            // Check if wali approval is still pending
            if ($interest->wali_approval_status === 'pending') {
                return back()->with('info', 'You accepted the interest request! Messaging will unlock as soon as your linked guardian (Wali) confirms approval (FR-4.3).');
            }

            // Otherwise, unlock conversation immediately!
            $conversation = $this->waliService->createOrUnlockConversation($interest);

            return redirect()->route('chat.show', $conversation->id)->with('success', 'Mutual interest confirmed! You may now begin halal conversation (FR-4.2).');
        }

        if ($action === 'decline') {
            $interest->update([
                'status' => 'declined',
                'responded_at' => now(),
            ]);
            AuditLog::record($user->id, 'interest_declined', 'Interest', $interest->id);

            return back()->with('info', 'Interest request politely declined.');
        }

        if ($action === 'ignore') {
            $interest->update(['status' => 'ignored']);

            return back()->with('info', 'Interest request moved to ignored.');
        }

        return back();
    }
}
