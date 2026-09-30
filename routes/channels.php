<?php

use App\Models\ConversationParticipant;
use App\Models\WaliLink;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{id}', function ($user, $id) {
    if ($user->isAdmin() || $user->isModerator()) {
        return true;
    }

    if (ConversationParticipant::where('conversation_id', $id)->where('user_id', $user->id)->exists()) {
        return true;
    }

    // Authorize if user is the active Wali of any participant in this conversation
    $participantUserIds = ConversationParticipant::where('conversation_id', $id)->pluck('user_id');

    return WaliLink::whereIn('seeker_user_id', $participantUserIds)
        ->where(function ($q) use ($user) {
            $q->where('wali_user_id', $user->id)->orWhere('wali_email', $user->email);
        })
        ->where('status', 'active')
        ->exists();
});
