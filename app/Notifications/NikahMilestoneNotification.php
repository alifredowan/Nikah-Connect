<?php

namespace App\Notifications;

use App\Models\Marriage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NikahMilestoneNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public Marriage $marriage,
        public string $event = 'initiated' // 'initiated', 'confirmed', 'declined'
    ) {}

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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $initiator = $this->marriage->initiator;

        if ($this->event === 'initiated') {
            return [
                'type' => 'nikah_milestone_initiated',
                'title' => '💍 Nikah Milestone Declared',
                'message' => "{$initiator->name} has declared that you have completed your Nikah! Please review and confirm this milestone.",
                'marriage_id' => $this->marriage->id,
                'icon' => '💍',
                'url' => route('marriages.celebration'),
            ];
        }

        if ($this->event === 'confirmed') {
            return [
                'type' => 'nikah_milestone_confirmed',
                'title' => '🎉 Nikah Mubarak!',
                'message' => 'Alhamdulillah! Your Nikah milestone is confirmed. May Allah bless your union with peace and barakah.',
                'marriage_id' => $this->marriage->id,
                'icon' => '🎉',
                'url' => route('marriages.celebration'),
            ];
        }

        return [
            'type' => 'nikah_milestone_declined',
            'title' => 'Nikah Declaration Status',
            'message' => 'The Nikah milestone confirmation request was declined.',
            'marriage_id' => $this->marriage->id,
            'icon' => 'ℹ️',
            'url' => route('chat.index'),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'nikah_milestone',
            'created_at' => now()->diffForHumans(),
            'read_at' => null,
            'data' => $this->toArray($notifiable),
        ]);
    }

    /**
     * Broadcast channel for the recipient.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->marriage->groom_id),
            new PrivateChannel('App.Models.User.'.$this->marriage->bride_id),
        ];
    }
}
