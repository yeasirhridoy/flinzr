<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

//Broadcast::channel('conversation.{conversation}', function (User $user, Conversation $conversation) {
//    return $conversation->conversationable->user_id === $user->id || $user->is_admin;
//});
