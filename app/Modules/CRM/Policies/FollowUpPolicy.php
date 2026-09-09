<?php

declare(strict_types=1);

namespace App\Modules\CRM\Policies;

use App\Models\User;
use App\Modules\CRM\Models\FollowUp;

class FollowUpPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('follow_up.view');
    }

    public function view(User $user, FollowUp $followUp): bool
    {
        return $user->can('follow_up.view')
            && $this->sameTenant($user, $followUp)
            && ($user->can('follow_up.view_all') || $this->isInvolved($user, $followUp));
    }

    public function create(User $user): bool
    {
        return $user->can('follow_up.create');
    }

    public function update(User $user, FollowUp $followUp): bool
    {
        return $user->can('follow_up.update')
            && $this->sameTenant($user, $followUp)
            && ($user->can('follow_up.view_all') || $this->isInvolved($user, $followUp));
    }

    public function delete(User $user, FollowUp $followUp): bool
    {
        return $user->can('follow_up.delete') && $this->sameTenant($user, $followUp);
    }

    private function isInvolved(User $user, FollowUp $followUp): bool
    {
        if ($followUp->created_by === $user->getKey()) {
            return true;
        }

        return $followUp->assignedEmployee !== null
            && $followUp->assignedEmployee->user_id === $user->getKey();
    }

    private function sameTenant(User $user, FollowUp $followUp): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $followUp->tenant_id;
    }
}
