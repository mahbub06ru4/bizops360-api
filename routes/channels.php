<?php

declare(strict_types=1);

use App\Modules\Notifications\Broadcasting\UserChannelAuthorizer;
use Illuminate\Support\Facades\Broadcast;

// Laravel's `broadcast` notification channel delivers to
// `private-App.Models.User.{id}` by default (User::receivesBroadcastNotificationsOn()
// is not overridden) — only the user themself may listen.
Broadcast::channel('App.Models.User.{id}', UserChannelAuthorizer::class);
