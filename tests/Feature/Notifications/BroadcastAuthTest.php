<?php

declare(strict_types=1);

use App\Modules\Notifications\Broadcasting\UserChannelAuthorizer;
use App\Modules\Notifications\Notifications\FollowUpDueNotification;
use App\Modules\Notifications\Notifications\TaskEventNotification;

it('authorizes only the channel owner on the private user notification channel', function (): void {
    $tenant = makeTenant();
    $user = makeUser($tenant);
    $other = makeUser($tenant);
    clearTenantContext();

    $authorizer = new UserChannelAuthorizer;

    expect($authorizer($user, (string) $user->id))->toBeTrue()
        ->and($authorizer($user, (string) $other->id))->toBeFalse();
});

it('requires authentication to reach the broadcasting auth endpoint', function (): void {
    $this->postJson('/api/v1/broadcasting/auth', [
        'channel_name' => 'private-App.Models.User.1',
        'socket_id' => '123.456',
    ])->assertUnauthorized();
});

it('delivers task and follow-up notifications on both the database and broadcast channels', function (): void {
    expect((new TaskEventNotification(1, 'Title', 'assigned', 'msg', null))->via((object) []))
        ->toBe(['database', 'broadcast'])
        ->and((new FollowUpDueNotification(1, 'Acme', 'call', '2026-01-01', null))->via((object) []))
        ->toBe(['database', 'broadcast']);
});
