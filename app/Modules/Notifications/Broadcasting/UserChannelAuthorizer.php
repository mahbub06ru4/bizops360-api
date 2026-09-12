<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Broadcasting;

use App\Models\User;

/**
 * Authorizes the private `App.Models.User.{id}` channel that Laravel's
 * `broadcast` notification channel delivers to by default — only the user
 * themself may listen. Extracted from routes/channels.php so it's a plain,
 * directly testable callable rather than only reachable through a live
 * Pusher/Reverb-protocol HTTP round trip.
 */
class UserChannelAuthorizer
{
    public function __invoke(User $user, int|string $id): bool
    {
        return $user->id === (int) $id;
    }
}
