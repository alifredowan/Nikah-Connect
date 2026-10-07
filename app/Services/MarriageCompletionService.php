<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\Interest;
use App\Models\Marriage;
use App\Models\PhotoAccessGrant;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\NikahMilestoneNotification;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MarriageCompletionService
{
    /**
     * Initiate a marriage confirmation request between two seekers.
     */
    public function initiate(User $initiator, int $spouseId, ?string $marriageDate = null, ?string $notes = null): Marriage
    {
        if ($initiator->id === $spouseId) {
            throw new InvalidArgumentException('You cannot initiate marriage with yourself.');
        }

        $spouse = User::findOrFail($spouseId);

        if ($initiator->gender && $spouse->gender && $initiator->gender === $spouse->gender) {
            throw new InvalidArgumentException('Marriage milestone must be between opposite gender candidates.');
        }

        if ($initiator->isMarried()) {
            throw new InvalidArgumentException('Your account is already marked as married.');
        }

        if ($spouse->isMarried()) {
            throw new InvalidArgumentException('The selected candidate account is already marked as married.');
        }

        // Determine Groom and Bride based on gender
        $groomId = $initiator->gender === 'male' ? $initiator->id : $spouse->id;
        $brideId = $initiator->gender === 'female' ? $initiator->id : $spouse->id;

        $existing = Marriage::where('groom_id', $groomId)
            ->where('bride_id', $brideId)
            ->first();

        if ($existing && $existing->isConfirmed()) {
            throw new InvalidArgumentException('This marriage milestone has already been confirmed.');
        }

        $marriage = Marriage::updateOrCreate(
            ['groom_id' => $groomId, 'bride_id' => $brideId],
            [
                'initiated_by_user_id' => $initiator->id,
                'status' => 'pending_confirmation',
                'marriage_date' => $marriageDate ?: now()->toDateString(),
                'confirmation_notes' => $notes,
            ]
        );

        AuditLog::record($initiator->id, 'nikah_initiated', 'Marriage', $marriage->id, [
            'groom_id' => $groomId,
            'bride_id' => $brideId,
        ]);

        $spouse->notify(new NikahMilestoneNotification($marriage, 'initiated'));

        return $marriage;
    }

    /**
     * Confirm a marriage, executing full post-nikah transitions for both IDs.
     */
    public function confirm(Marriage $marriage, User $confirmer): void
    {
        if ($marriage->status === 'confirmed') {
            return;
        }

        $isSpouse = ($confirmer->id === $marriage->groom_id || $confirmer->id === $marriage->bride_id);
        $isWali = false;
        if (! $isSpouse && $confirmer->isWali()) {
            $isWali = $confirmer->waliLinksAsWali()
                ->whereIn('seeker_user_id', [$marriage->groom_id, $marriage->bride_id])
                ->where('status', 'active')
                ->exists();
        }

        if (! $isSpouse && ! $isWali && ! $confirmer->isAdmin()) {
            throw new InvalidArgumentException('You are not authorized to confirm this marriage.');
        }

        DB::transaction(function () use ($marriage, $confirmer) {
            $groom = $marriage->groom;
            $bride = $marriage->bride;

            // 1. Mark Marriage as Confirmed
            $marriage->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            // 2. Transition Marital Status for Both IDs to Married
            $groom->update(['marital_status' => 'married']);
            $bride->update(['marital_status' => 'married']);

            // 3. Mark active subscriptions as completed_nikah
            Subscription::whereIn('user_id', [$groom->id, $bride->id])
                ->where('status', 'active')
                ->update(['status' => 'completed_nikah']);

            // 4. Auto-Close all OTHER pending/accepted interests with third parties
            Interest::where(function ($q) use ($groom, $bride) {
                $q->where(function ($sub) use ($groom, $bride) {
                    $sub->where('sender_id', $groom->id)->where('recipient_id', '!=', $bride->id);
                })->orWhere(function ($sub) use ($groom, $bride) {
                    $sub->where('recipient_id', $groom->id)->where('sender_id', '!=', $bride->id);
                })->orWhere(function ($sub) use ($groom, $bride) {
                    $sub->where('sender_id', $bride->id)->where('recipient_id', '!=', $groom->id);
                })->orWhere(function ($sub) use ($groom, $bride) {
                    $sub->where('recipient_id', $bride->id)->where('sender_id', '!=', $groom->id);
                });
            })->whereIn('status', ['pending', 'accepted'])
                ->update(['status' => 'closed_married']);

            // 5. Lock third-party conversations to read-only (keeping couple conversation active)
            $coupleInterest = Interest::where(function ($q) use ($groom, $bride) {
                $q->where('sender_id', $groom->id)->where('recipient_id', $bride->id);
            })->orWhere(function ($q) use ($groom, $bride) {
                $q->where('sender_id', $bride->id)->where('recipient_id', $groom->id);
            })->first();

            $coupleConversationId = $coupleInterest?->conversation?->id;

            $thirdPartyConversations = Conversation::whereHas('participants', function ($q) use ($groom, $bride) {
                $q->whereIn('user_id', [$groom->id, $bride->id]);
            });

            if ($coupleConversationId) {
                $thirdPartyConversations->where('id', '!=', $coupleConversationId);
            }

            $thirdPartyConversations->update([
                'status' => 'locked',
                'locked_reason' => 'candidate_married',
            ]);

            // 6. Revoke third-party photo grants
            $groomProfileId = $groom->profile?->id;
            $brideProfileId = $bride->profile?->id;

            $profileIds = array_filter([$groomProfileId, $brideProfileId]);
            if (! empty($profileIds)) {
                PhotoAccessGrant::whereIn('profile_id', $profileIds)
                    ->whereNotIn('granted_to_user_id', [$groom->id, $bride->id])
                    ->update(['status' => 'revoked']);
            }

            PhotoAccessGrant::whereIn('granted_to_user_id', [$groom->id, $bride->id])
                ->whereNotIn('profile_id', $profileIds)
                ->update(['status' => 'revoked']);

            // 7. Record Audit Log
            AuditLog::record($confirmer->id, 'nikah_confirmed', 'Marriage', $marriage->id, [
                'groom_id' => $groom->id,
                'bride_id' => $bride->id,
            ]);
        });

        // Notify both spouses of confirmation
        $marriage->groom->notify(new NikahMilestoneNotification($marriage, 'confirmed'));
        $marriage->bride->notify(new NikahMilestoneNotification($marriage, 'confirmed'));
    }

    /**
     * Decline a pending marriage confirmation.
     */
    public function decline(Marriage $marriage, User $decliner): void
    {
        $marriage->update(['status' => 'declined']);

        AuditLog::record($decliner->id, 'nikah_declined', 'Marriage', $marriage->id);

        $spouse = $marriage->spouseOf($decliner);
        $spouse?->notify(new NikahMilestoneNotification($marriage, 'declined'));
    }

    /**
     * Submit or update a couple's Barakah Success Story.
     */
    public function submitStory(Marriage $marriage, User $user, string $title, string $body, bool $isPublic = true): void
    {
        if ($user->id !== $marriage->groom_id && $user->id !== $marriage->bride_id) {
            throw new InvalidArgumentException('Only the married couple can share their Barakah story.');
        }

        $marriage->update([
            'story_title' => $title,
            'story_body' => $body,
            'story_is_public' => $isPublic,
        ]);

        AuditLog::record($user->id, 'nikah_story_submitted', 'Marriage', $marriage->id);
    }
}
