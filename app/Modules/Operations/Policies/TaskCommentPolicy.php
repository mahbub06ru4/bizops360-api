<?php

declare(strict_types=1);

namespace App\Modules\Operations\Policies;

use App\Models\User;
use App\Modules\Operations\Models\TaskComment;

class TaskCommentPolicy
{
    public function update(User $user, TaskComment $comment): bool
    {
        return $this->sameTenant($user, $comment) && $comment->author_id === $user->getKey();
    }

    public function delete(User $user, TaskComment $comment): bool
    {
        return $this->sameTenant($user, $comment)
            && ($comment->author_id === $user->getKey() || $user->can('task.view_all'));
    }

    private function sameTenant(User $user, TaskComment $comment): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $comment->tenant_id;
    }
}
