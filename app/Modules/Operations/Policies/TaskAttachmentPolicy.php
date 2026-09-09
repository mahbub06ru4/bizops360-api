<?php

declare(strict_types=1);

namespace App\Modules\Operations\Policies;

use App\Models\User;
use App\Modules\Operations\Models\TaskAttachment;

class TaskAttachmentPolicy
{
    public function delete(User $user, TaskAttachment $attachment): bool
    {
        return $this->sameTenant($user, $attachment)
            && ($attachment->uploaded_by === $user->getKey() || $user->can('task.view_all'));
    }

    private function sameTenant(User $user, TaskAttachment $attachment): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $attachment->tenant_id;
    }
}
