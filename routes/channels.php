<?php

use Illuminate\Support\Facades\Broadcast;

// (new PrivateChannel('user.'.$id) w app/Events/*).
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
