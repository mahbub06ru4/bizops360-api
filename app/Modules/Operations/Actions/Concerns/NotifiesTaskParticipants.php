<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions\Concerns;

use App\Models\User;
use App\Modules\Notifications\Notifications\TaskEventNotification;
use App\Modules\Operations\Models\Task;
use App\Modules\Organization\Models\Employee;
use Illuminate\Support\Facades\Notification;

/**
 * Sends a {@see TaskEventNotification} to the people who care about a task —
 * its creator and current assignee (resolved to their linked user) — never the
 * person who triggered the event.
 */
trait NotifiesTaskParticipants
{
    protected function notifyTaskEvent(
        Task $task,
        string $event,
        string $message,
        ?User $actor,
        ?int $onlyAssigneeEmployeeId = null,
    ): void {
        $userIds = [];

        if ($onlyAssigneeEmployeeId !== null) {
            $userIds[] = $this->userIdForEmployee($onlyAssigneeEmployeeId);
        } else {
            $userIds[] = $task->created_by;
            $userIds[] = $this->userIdForEmployee($task->assignee_employee_id);
        }

        $actorId = $actor !== null ? (int) $actor->getKey() : null;

        $recipients = collect($userIds)
            ->filter()
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->reject(static fn (int $id): bool => $id === $actorId)
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        $users = User::query()->whereIn('id', $recipients->all())->get();

        Notification::send($users, new TaskEventNotification(
            (int) $task->getKey(),
            $task->title,
            $event,
            $message,
            $actor?->name,
        ));
    }

    private function userIdForEmployee(?int $employeeId): ?int
    {
        if ($employeeId === null) {
            return null;
        }

        $id = Employee::query()->whereKey($employeeId)->value('user_id');

        return $id === null ? null : (int) $id;
    }
}
