<?php

namespace Tests\Feature;

use App\Events\MessageSentEvent;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Interest;
use App\Models\Profile;
use App\Models\User;
use App\Models\WaliLink;
use App\Notifications\InterestReceivedNotification;
use App\Notifications\InterestRespondedNotification;
use App\Notifications\NewChatMessageNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class InterestNotificationAndReverbTest extends TestCase
{
    use DatabaseTransactions;

    private function createSeeker(?string $name = null): User
    {
        $user = User::factory()->create([
            'name' => $name ?? 'Seeker '.Str::random(5),
            'email' => 'seeker.'.Str::random(8).'@example.com',
            'role' => 'seeker',
            'is_verified' => true,
            'is_active' => true,
        ]);

        Profile::create([
            'user_id' => $user->id,
            'gender' => 'male',
            'wali_required' => false,
            'bio' => 'Practicing Muslim seeker',
            'city' => 'Dhaka',
            'country' => 'Bangladesh',
        ]);

        return $user;
    }

    private function linkWaliToSeeker(User $seeker, ?string $waliName = null): User
    {
        $wali = User::factory()->create([
            'name' => $waliName ?? 'Wali of '.$seeker->name,
            'email' => 'wali.'.Str::random(8).'@example.com',
            'role' => 'wali',
            'is_verified' => true,
            'is_active' => true,
        ]);

        WaliLink::create([
            'seeker_user_id' => $seeker->id,
            'wali_user_id' => $wali->id,
            'wali_name' => $wali->name,
            'wali_email' => $wali->email,
            'relationship_type' => 'father',
            'permission_level' => 'approve_required',
            'status' => 'active',
            'invite_token' => Str::random(32),
        ]);

        $seeker->profile?->update(['wali_required' => true]);

        return $wali;
    }

    public function test_interest_sent_dispatches_notification_to_recipient(): void
    {
        Notification::fake();

        $sender = $this->createSeeker('Farhan');
        $recipient = $this->createSeeker('Ayesha');

        $response = $this->actingAs($sender)->post(route('interests.send', $recipient->id), [
            'note' => 'Salam, I would like to express interest in your profile.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interests', [
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => 'pending',
        ]);

        Notification::assertSentTo($recipient, InterestReceivedNotification::class, function ($notification) use ($sender) {
            return $notification->interest->sender_id === $sender->id && ! $notification->isWali;
        });
    }

    public function test_interest_sent_dispatches_notification_to_both_walis_if_dependent(): void
    {
        Notification::fake();

        $sender = $this->createSeeker('Bilal');
        $senderWali = $this->linkWaliToSeeker($sender, 'Father of Bilal');

        $recipient = $this->createSeeker('Fatima');
        $recipientWali = $this->linkWaliToSeeker($recipient, 'Father of Fatima');

        $this->actingAs($sender)->post(route('interests.send', $recipient->id), [
            'note' => 'Respectful matrimonial inquiry',
        ]);

        // Recipient gets notified
        Notification::assertSentTo($recipient, InterestReceivedNotification::class);

        // Recipient's Wali gets notified
        Notification::assertSentTo($recipientWali, InterestReceivedNotification::class, function ($notification) {
            return $notification->isWali === true && ! $notification->isSenderWali;
        });

        // Sender's Wali gets notified
        Notification::assertSentTo($senderWali, InterestReceivedNotification::class, function ($notification) {
            return $notification->isWali === true && $notification->isSenderWali === true;
        });
    }

    public function test_accepting_interest_unlocks_conversation_and_notifies_sender_and_walis(): void
    {
        Notification::fake();

        $sender = $this->createSeeker('Tariq');
        $senderWali = $this->linkWaliToSeeker($sender, 'Uncle Tariq');

        $recipient = $this->createSeeker('Maryam');
        $recipientWali = $this->linkWaliToSeeker($recipient, 'Father Maryam');

        $interest = Interest::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => 'pending',
            'wali_approval_status' => 'not_required',
        ]);

        $response = $this->actingAs($recipient)->post(route('interests.respond', $interest->id), [
            'action' => 'accept',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interests', [
            'id' => $interest->id,
            'status' => 'accepted',
        ]);

        // Conversation should be created with both seekers and chaperone walis
        $conversation = Conversation::where('interest_id', $interest->id)->first();
        $this->assertNotNull($conversation);

        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'role' => 'seeker',
        ]);
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $recipient->id,
            'role' => 'seeker',
        ]);
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $recipientWali->id,
            'role' => 'wali_chaperone',
        ]);
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $senderWali->id,
            'role' => 'wali_chaperone',
        ]);

        // Sender receives accepted notification
        Notification::assertSentTo($sender, InterestRespondedNotification::class, function ($n) {
            return $n->action === 'accepted';
        });

        // Walis receive notifications
        Notification::assertSentTo($recipientWali, InterestRespondedNotification::class, function ($n) {
            return $n->action === 'accepted' && $n->isWali === true;
        });
        Notification::assertSentTo($senderWali, InterestRespondedNotification::class, function ($n) {
            return $n->action === 'accepted' && $n->isWali === true;
        });
    }

    public function test_declining_interest_notifies_sender_and_walis(): void
    {
        Notification::fake();

        $sender = $this->createSeeker('Hamza');
        $senderWali = $this->linkWaliToSeeker($sender, 'Brother Hamza');

        $recipient = $this->createSeeker('Zainab');
        $recipientWali = $this->linkWaliToSeeker($recipient, 'Father Zainab');

        $interest = Interest::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => 'pending',
            'wali_approval_status' => 'not_required',
        ]);

        $this->actingAs($recipient)->post(route('interests.respond', $interest->id), [
            'action' => 'decline',
        ]);

        $this->assertDatabaseHas('interests', [
            'id' => $interest->id,
            'status' => 'declined',
        ]);

        Notification::assertSentTo($sender, InterestRespondedNotification::class, function ($n) {
            return $n->action === 'declined';
        });
        Notification::assertSentTo($recipientWali, InterestRespondedNotification::class, function ($n) {
            return $n->action === 'declined';
        });
        Notification::assertSentTo($senderWali, InterestRespondedNotification::class, function ($n) {
            return $n->action === 'declined';
        });
    }

    public function test_send_message_broadcasts_reverb_event_and_notifies_participants(): void
    {
        Event::fake([MessageSentEvent::class]);
        Notification::fake();

        $sender = $this->createSeeker('Ali');
        $recipient = $this->createSeeker('Khadija');
        $wali = $this->linkWaliToSeeker($recipient, 'Wali Khadija');

        $interest = Interest::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => 'accepted',
            'wali_approval_status' => 'approved',
        ]);

        $conversation = Conversation::create([
            'interest_id' => $interest->id,
            'status' => 'active',
        ]);

        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $sender->id, 'role' => 'seeker']);
        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $recipient->id, 'role' => 'seeker']);
        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $wali->id, 'role' => 'wali_chaperone']);

        $response = $this->actingAs($sender)->postJson(route('chat.send', $conversation->id), [
            'body' => 'Assalamu Alaikum wa Rahmatullah. How are you and your family?',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => 'Assalamu Alaikum wa Rahmatullah. How are you and your family?',
        ]);

        // Verify Reverb WebSocket MessageSentEvent dispatched
        Event::assertDispatched(MessageSentEvent::class, function ($event) use ($conversation, $sender) {
            return $event->message->conversation_id === $conversation->id
                && $event->message->sender_id === $sender->id;
        });

        // Verify recipient and chaperone Wali received notification
        Notification::assertSentTo($recipient, NewChatMessageNotification::class);
        Notification::assertSentTo($wali, NewChatMessageNotification::class, function ($n) {
            return $n->isWali === true;
        });
    }

    public function test_notification_api_endpoints_work_correctly(): void
    {
        $user = $this->createSeeker('Zayd');
        $sender = $this->createSeeker('Amina');

        $interest = Interest::create([
            'sender_id' => $sender->id,
            'recipient_id' => $user->id,
            'status' => 'pending',
            'wali_approval_status' => 'not_required',
        ]);

        // Send real notification to DB
        $user->notify(new InterestReceivedNotification($interest, targetUserId: $user->id));

        $this->assertEquals(1, $user->unreadNotifications()->count());

        // Fetch via JSON
        $response = $this->actingAs($user)->getJson(route('notifications.index'));
        $response->assertOk();
        $response->assertJsonStructure([
            'unread_count',
            'notifications',
        ]);
        $response->assertJson(['unread_count' => 1]);

        $notificationId = $user->unreadNotifications()->first()->id;

        // Mark as read
        $readResponse = $this->actingAs($user)->postJson(route('notifications.read', $notificationId));
        $readResponse->assertOk();
        $readResponse->assertJson(['unread_count' => 0]);

        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }
}
