<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Notifications;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * A notification about something that happened to a task the recipient
 * created or is assigned to. Carries only primitives so the Notifications module
 * stays free of a dependency on Operations. Persisted (database) so it survives
 * a disconnected client, and broadcast (Reverb) so a connected client updates
 * live — see routes/channels.php for the private per-user channel.
 */
class TaskEventNotification extends Notification
{
    public function __construct(
        private readonly int $taskId,
        private readonly string $taskTitle,
        private readonly string $event,
        private readonly string $message,
        private readonly ?string $actorName,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->taskId,
            'task_title' => $this->taskTitle,
            'event' => $this->event,
            'message' => $this->message,
            'actor_name' => $this->actorName,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
