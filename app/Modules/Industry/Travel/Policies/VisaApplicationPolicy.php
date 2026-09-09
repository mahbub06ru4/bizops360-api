<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Policies;

use App\Models\User;
use App\Modules\Industry\Travel\Models\VisaApplication;

class VisaApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('visa_application.view');
    }

    public function view(User $user, VisaApplication $application): bool
    {
        return $user->can('visa_application.view')
            && $this->sameTenant($user, $application)
            && ($user->can('visa_application.view_all') || $this->isInvolved($user, $application));
    }

    public function create(User $user): bool
    {
        return $user->can('visa_application.create');
    }

    public function update(User $user, VisaApplication $application): bool
    {
        return $user->can('visa_application.update')
            && $this->sameTenant($user, $application)
            && ($user->can('visa_application.view_all') || $this->isInvolved($user, $application));
    }

    public function submit(User $user, VisaApplication $application): bool
    {
        return $user->can('visa_application.submit') && $this->sameTenant($user, $application);
    }

    public function decide(User $user, VisaApplication $application): bool
    {
        return $user->can('visa_application.decide') && $this->sameTenant($user, $application);
    }

    public function delete(User $user, VisaApplication $application): bool
    {
        return $user->can('visa_application.delete') && $this->sameTenant($user, $application);
    }

    private function isInvolved(User $user, VisaApplication $application): bool
    {
        if ($application->created_by === $user->getKey()) {
            return true;
        }

        return $application->assignee !== null && $application->assignee->user_id === $user->getKey();
    }

    private function sameTenant(User $user, VisaApplication $application): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $application->tenant_id;
    }
}
