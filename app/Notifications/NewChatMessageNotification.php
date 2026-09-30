<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewChatMessageNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public Message $message,
        public int $targetUserId,
        public bool $isWali = false
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
     * Data stored in database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $sender = $this->message->sender;

        return [
            'type' => $this->isWali ? 'wali_chaperoned_message' : 'chat_message',
            'title' => $this->isWali ? 'Chaperoned Message Sent' : "Message from {$sender->name}",
            'message' => Str::limit($this->message->body, 80),
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'action_url' => route('chat.show', $this->message->conversation_id),
            'icon' => $this->isWali ? '🛡️' : '💬',
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
        ];
    }
}
