<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\Interest;
use App\Models\User;
use App\Notifications\InterestReceivedNotification;
use App\Notifications\InterestRespondedNotification;
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

        $received = Interest::with(['sender.profile.primaryPhoto', 'conversation'])
            ->where('recipient_id', $user->id)
            ->latest()
            ->get();

        $sent = Interest::with(['recipient.profile.primaryPhoto', 'conversation'])
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

        if ($recipient->role !== 'seeker' || ! $recipient->is_active) {
            return back()->with('error', 'This user is not accepting matrimonial interest requests.');
        }

        if ($sender->isMarried()) {
            return back()->with('error', 'Your profile is currently marked as married. Married accounts cannot send new matrimonial proposals.');
        }

        if ($recipient->isMarried()) {
            return back()->with('error', 'This candidate has completed their Nikah through Nikah Connect and is no longer accepting new proposals.');
        }

        if ($sender->gender && $recipient->gender && $sender->gender === $recipient->gender) {
            return back()->with('error', 'Interest requests can only be sent to candidates of the opposite gender.');
        }

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

        // Dispatch notifications via Laravel Reverb
        // 1. Notify Recipient
        $recipient->notify(new InterestReceivedNotification(
            interest: $interest,
            isWali: false,
            targetUserId: $recipient->id
        ));

        // 2. Notify Recipient's Wali if recipient has a linked guardian
        $recipientWali = $recipient->getActiveWaliUser();
        if ($recipientWali) {
            $recipientWali->notify(new InterestReceivedNotification(
                interest: $interest,
                isWali: true,
                forSeeker: $recipient,
                targetUserId: $recipientWali->id
            ));
        }

        // 3. Notify Sender's Wali if sender has a linked guardian
        $senderWali = $sender->getActiveWaliUser();
        if ($senderWali) {
            $senderWali->notify(new InterestReceivedNotification(
                interest: $interest,
                isWali: true,
                forSeeker: $sender,
                isSenderWali: true,
                targetUserId: $senderWali->id
            ));
        }

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
        $interest = Interest::with(['sender', 'recipient'])->where('recipient_id', $user->id)->findOrFail($interestId);

        // Guard: Prevent multiple responses on an already accepted or declined interest
        if ($interest->status !== 'pending') {
            return back()->with('info', "This interest request has already been {$interest->status}.");
        }

        // Auto-mark any unread notifications related to this interest as read
        $user->unreadNotifications->each(function ($n) use ($interest) {
            if (($n->data['interest_id'] ?? null) == $interest->id) {
                $n->markAsRead();
            }
        });

        $sender = $interest->sender;
        $recipient = $user;

        $recipientWali = $recipient->getActiveWaliUser();
        $senderWali = $sender->getActiveWaliUser();

        $action = $request->action;

        if ($action === 'accept') {
            $interest->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            AuditLog::record($user->id, 'interest_accepted', 'Interest', $interest->id);

            // Check if wali approval is still pending
            if ($interest->wali_approval_status === 'pending') {
                // Notify sender that recipient accepted and awaits Wali confirmation
                $sender->notify(new InterestRespondedNotification(
                    interest: $interest,
                    action: 'accepted',
                    targetUserId: $sender->id
                ));

                // Notify recipient's Wali
                if ($recipientWali) {
                    $recipientWali->notify(new InterestRespondedNotification(
                        interest: $interest,
                        action: 'accepted',
                        isWali: true,
                        forSeeker: $recipient,
                        targetUserId: $recipientWali->id
                    ));
                }

                // Notify sender's Wali
                if ($senderWali) {
                    $senderWali->notify(new InterestRespondedNotification(
                        interest: $interest,
                        action: 'accepted',
                        isWali: true,
                        forSeeker: $sender,
                        targetUserId: $senderWali->id
                    ));
                }

                return back()->with('info', 'You accepted the interest request! Messaging will unlock as soon as your linked guardian (Wali) confirms approval (FR-4.3).');
            }

            // Otherwise, unlock conversation immediately!
            $conversation = $this->waliService->createOrUnlockConversation($interest);

            // Notify sender that request was accepted and conversation is unlocked
            $sender->notify(new InterestRespondedNotification(
                interest: $interest,
                action: 'accepted',
                conversation: $conversation,
                targetUserId: $sender->id
            ));

            // Notify recipient's Wali
            if ($recipientWali) {
                $recipientWali->notify(new InterestRespondedNotification(
                    interest: $interest,
                    action: 'accepted',
                    isWali: true,
                    conversation: $conversation,
                    forSeeker: $recipient,
                    targetUserId: $recipientWali->id
                ));
            }

            // Notify sender's Wali
            if ($senderWali) {
                $senderWali->notify(new InterestRespondedNotification(
                    interest: $interest,
                    action: 'accepted',
                    isWali: true,
                    conversation: $conversation,
                    forSeeker: $sender,
                    targetUserId: $senderWali->id
                ));
            }

            return redirect()->route('chat.show', $conversation->id)->with('success', 'Mutual interest confirmed! You may now begin halal conversation (FR-4.2).');
        }

        if ($action === 'decline') {
            $interest->update([
                'status' => 'declined',
                'responded_at' => now(),
            ]);
            AuditLog::record($user->id, 'interest_declined', 'Interest', $interest->id);

            // Notify sender that request was declined
            $sender->notify(new InterestRespondedNotification(
                interest: $interest,
                action: 'declined',
                targetUserId: $sender->id
            ));

            // Notify recipient's Wali
            if ($recipientWali) {
                $recipientWali->notify(new InterestRespondedNotification(
                    interest: $interest,
                    action: 'declined',
                    isWali: true,
                    forSeeker: $recipient,
                    targetUserId: $recipientWali->id
                ));
            }

            // Notify sender's Wali
            if ($senderWali) {
                $senderWali->notify(new InterestRespondedNotification(
                    interest: $interest,
                    action: 'declined',
                    isWali: true,
                    forSeeker: $sender,
                    targetUserId: $senderWali->id
                ));
            }

            return back()->with('info', 'Interest request politely declined.');
        }

        if ($action === 'ignore') {
            $interest->update(['status' => 'ignored']);

            return back()->with('info', 'Interest request moved to ignored.');
        }

        return back();
    }
}
