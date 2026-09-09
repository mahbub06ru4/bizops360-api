<?php

declare(strict_types=1);

namespace App\Modules\CRM\Policies;

use App\Models\User;
use App\Modules\CRM\Models\Lead;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('lead.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('lead.view')
            && $this->sameTenant($user, $lead)
            && ($user->can('lead.view_all') || $this->isInvolved($user, $lead));
    }

    public function create(User $user): bool
    {
        return $user->can('lead.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('lead.update')
            && $this->sameTenant($user, $lead)
            && ($user->can('lead.view_all') || $this->isInvolved($user, $lead));
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $user->can('lead.convert') && $this->sameTenant($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('lead.delete') && $this->sameTenant($user, $lead);
    }

    private function isInvolved(User $user, Lead $lead): bool
    {
        if ($lead->created_by === $user->getKey()) {
            return true;
        }

        return $lead->owner !== null && $lead->owner->user_id === $user->getKey();
    }

    private function sameTenant(User $user, Lead $lead): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $lead->tenant_id;
    }
}
