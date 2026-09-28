<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Allow authenticated users to subscribe to the real-time sales channel
Broadcast::channel('sales', function ($user) {
    return (bool) $user;
});

// Allow authenticated users to subscribe to the real-time purchases channel
Broadcast::channel('purchases', function ($user) {
    return (bool) $user;
});
