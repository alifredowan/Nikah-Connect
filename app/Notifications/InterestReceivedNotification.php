<?php

namespace App\Notifications;

use App\Models\Interest;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class InterestReceivedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public int $targetUserId;

    public function __construct(
        public Interest $interest,
        public bool $isWali = false,
        public ?User $forSeeker = null,
        public bool $isSenderWali = false,
        ?int $targetUserId = null
    ) {
        $this->targetUserId = $targetUserId ?? $this->interest->recipient_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Data stored in database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $sender = $this->interest->sender;
        $recipient = $this->interest->recipient;

        if ($this->isSenderWali) {
            return [
                'type' => 'wali_interest_sent',
                'title' => 'Dependent Expressed Interest',
                'message' => "Your dependent {$sender->name} sent an interest request to {$recipient->name}.",
                'interest_id' => $this->interest->id,
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'action_url' => route('wali.dashboard'),
                'icon' => '🛡️',
            ];
        }

        if ($this->isWali) {
            return [
                'type' => 'wali_interest_received',
                'title' => 'New Interest for Dependent',
                'message' => "A new interest request has been sent to your dependent {$recipient->name} by {$sender->name}.",
                'interest_id' => $this->interest->id,
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'action_url' => route('wali.dashboard'),
                'icon' => '🛡️',
            ];
        }

        return [
            'type' => 'interest_received',
            'title' => 'New Interest Request Received',
            'message' => "{$sender->name} has sent you an expression of interest!",
            'note' => $this->interest->message_note,
            'interest_id' => $this->interest->id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'action_url' => route('interests.index'),
            'icon' => '💌',
        ];
    }

    /**
     * Real-time Broadcast payload via Laravel Reverb.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $data = $this->toArray($notifiable);
        $data['id'] = $this->id;
        $data['created_at'] = now()->diffForHumans();

        return new BroadcastMessage($data);
    }

    /**
     * The channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->targetUserId),
            new PrivateChannel('user.'.$this->targetUserId),
        ];
    }
}
