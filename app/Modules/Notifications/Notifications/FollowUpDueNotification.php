<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Notifications;

use Illuminate\Notifications\Notification;

/**
 * A database reminder that a CRM follow-up is coming due. Primitives only.
 */
class FollowUpDueNotification extends Notification
{
    public function __construct(
        private readonly int $followUpId,
        private readonly string $subjectLabel,
        private readonly string $type,
        private readonly string $dueAt,
        private readonly ?string $notes,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'follow_up_id' => $this->followUpId,
            'subject' => $this->subjectLabel,
            'type' => $this->type,
            'due_at' => $this->dueAt,
            'notes' => $this->notes,
            'message' => "{$this->type} follow-up for {$this->subjectLabel} is due.",
        ];
    }
}
