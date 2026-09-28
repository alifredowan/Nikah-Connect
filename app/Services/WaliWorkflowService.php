<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Interest;
use App\Models\User;
use App\Models\WaliLink;
use App\Notifications\InterestRespondedNotification;

class WaliWorkflowService
{
    /**
     * Invite or link a Wali to a seeker profile (FR-4.3, 6.2).
     */
    public function linkWali(
        User $seeker,
        string $relationshipType,
        string $permissionLevel = 'approve_required',
        ?string $waliName = null,
        ?string $waliEmail = null,
        ?string $waliPhone = null
    ): WaliLink {
        // If an existing user matches the email/phone, link directly
        $waliUser = null;
        if ($waliEmail) {
            $waliUser = User::where('email', $waliEmail)->first();
        }

        return WaliLink::create([
            'seeker_user_id' => $seeker->id,
            'wali_user_id' => $waliUser?->id,
            'wali_name' => $waliName,
            'wali_email' => $waliEmail,
            'wali_phone' => $waliPhone,
            'relationship_type' => $relationshipType,
            'permission_level' => $permissionLevel,
            'status' => $waliUser ? 'active' : 'pending',
            'invite_token' => bin2hex(random_bytes(16)),
        ]);
    }

    /**
     * Determine if an interest request requires Wali approval before conversation unlock.
     */
    public function requiresWaliApproval(User $recipient): bool
    {
        $activeLink = WaliLink::where('seeker_user_id', $recipient->id)
            ->where('status', 'active')
            ->where('permission_level', 'approve_required')
            ->exists();

        $profileRequirement = $recipient->profile?->wali_required ?? false;

        return $activeLink || $profileRequirement;
    }

    /**
     * Approve an interest request by Wali.
     */
    public function approveByWali(Interest $interest, User $wali): bool
    {
        // Check if wali is authorized for recipient
        $isAuthorized = WaliLink::where('seeker_user_id', $interest->recipient_id)
            ->where('wali_user_id', $wali->id)
            ->where('status', 'active')
            ->exists();

        if (! $isAuthorized && ! $wali->isAdmin()) {
            return false;
        }

        $interest->update([
            'wali_approval_status' => 'approved',
            'wali_id' => $wali->id,
        ]);

        // If recipient also accepted, unlock conversation
        if ($interest->status === 'accepted') {
            $conversation = $this->createOrUnlockConversation($interest);

            $interest->load(['sender', 'recipient']);
            $sender = $interest->sender;
            $recipient = $interest->recipient;

            // Notify both seekers
            $sender->notify(new InterestRespondedNotification(
                interest: $interest,
                action: 'accepted',
                conversation: $conversation,
                targetUserId: $sender->id
            ));

            $recipient->notify(new InterestRespondedNotification(
                interest: $interest,
                action: 'accepted',
                conversation: $conversation,
                targetUserId: $recipient->id
            ));

            // Notify sender's wali if any
            $senderWali = $sender->getActiveWaliUser();
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
        }

        return true;
    }

    /**
     * Reject an interest request by Wali.
     */
    public function rejectByWali(Interest $interest, User $wali): bool
    {
        $interest->update([
            'wali_approval_status' => 'rejected',
            'wali_id' => $wali->id,
            'status' => 'declined',
        ]);

        $interest->load(['sender', 'recipient']);
        $sender = $interest->sender;
        $recipient = $interest->recipient;

        // Notify sender and recipient of guardian rejection
        $sender->notify(new InterestRespondedNotification(
            interest: $interest,
            action: 'declined',
            targetUserId: $sender->id
        ));

        $recipient->notify(new InterestRespondedNotification(
            interest: $interest,
            action: 'declined',
            targetUserId: $recipient->id
        ));

        // Notify sender's wali if any
        $senderWali = $sender->getActiveWaliUser();
        if ($senderWali) {
            $senderWali->notify(new InterestRespondedNotification(
                interest: $interest,
                action: 'declined',
                isWali: true,
                forSeeker: $sender,
                targetUserId: $senderWali->id
            ));
        }

        return true;
    }

    /**
     * Create conversation between participants and add Wali as chaperone observer.
     */
    public function createOrUnlockConversation(Interest $interest): Conversation
    {
        $conversation = Conversation::firstOrCreate([
            'interest_id' => $interest->id,
        ], [
            'status' => 'active',
        ]);

        // Ensure both seekers are participants
        ConversationParticipant::firstOrCreate([
            'conversation_id' => $conversation->id,
            'user_id' => $interest->sender_id,
        ], [
            'role' => 'seeker',
        ]);

        ConversationParticipant::firstOrCreate([
            'conversation_id' => $conversation->id,
            'user_id' => $interest->recipient_id,
        ], [
            'role' => 'seeker',
        ]);

        // If recipient has an active Wali, add Wali as chaperone observer
        $recipientWaliLink = WaliLink::where('seeker_user_id', $interest->recipient_id)
            ->where('status', 'active')
            ->whereNotNull('wali_user_id')
            ->first();

        if ($recipientWaliLink && $recipientWaliLink->wali_user_id) {
            ConversationParticipant::firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id' => $recipientWaliLink->wali_user_id,
            ], [
                'role' => 'wali_chaperone',
            ]);
        }

        // If sender has an active Wali, add sender's Wali as chaperone observer too
        $senderWaliLink = WaliLink::where('seeker_user_id', $interest->sender_id)
            ->where('status', 'active')
            ->whereNotNull('wali_user_id')
            ->first();

        if ($senderWaliLink && $senderWaliLink->wali_user_id) {
            ConversationParticipant::firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id' => $senderWaliLink->wali_user_id,
            ], [
                'role' => 'wali_chaperone',
            ]);
        }

        return $conversation;
    }
}
