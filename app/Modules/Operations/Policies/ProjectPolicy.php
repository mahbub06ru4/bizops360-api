<?php

declare(strict_types=1);

namespace App\Modules\Operations\Policies;

use App\Models\User;
use App\Modules\Operations\Models\Project;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('project.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('project.view') && $this->sameTenant($user, $project);
    }

    public function create(User $user): bool
    {
        return $user->can('project.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('project.update') && $this->sameTenant($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('project.delete') && $this->sameTenant($user, $project);
    }

    private function sameTenant(User $user, Project $project): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $project->tenant_id;
    }
}
