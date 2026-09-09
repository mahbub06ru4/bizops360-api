<?php

declare(strict_types=1);

namespace App\Modules\Operations\Policies;

use App\Models\User;
use App\Modules\Operations\Models\Task;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('task.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('task.view')
            && $this->sameTenant($user, $task)
            && ($user->can('task.view_all') || $this->isInvolved($user, $task));
    }

    public function create(User $user): bool
    {
        return $user->can('task.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('task.update')
            && $this->sameTenant($user, $task)
            && ($user->can('task.view_all') || $this->isInvolved($user, $task));
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->can('task.assign') && $this->sameTenant($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('task.delete') && $this->sameTenant($user, $task);
    }

    private function isInvolved(User $user, Task $task): bool
    {
        if ($task->created_by === $user->getKey()) {
            return true;
        }

        return $task->assigneeEmployee !== null
            && $task->assigneeEmployee->user_id === $user->getKey();
    }

    private function sameTenant(User $user, Task $task): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $task->tenant_id;
    }
}
