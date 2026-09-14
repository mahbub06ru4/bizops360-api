<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('real_estate_project.view');
    }

    public function view(User $user, RealEstateProject $project): bool
    {
        return $user->can('real_estate_project.view') && $this->sameTenant($user, $project);
    }

    public function create(User $user): bool
    {
        return $user->can('real_estate_project.create');
    }

    public function update(User $user, RealEstateProject $project): bool
    {
        return $user->can('real_estate_project.update') && $this->sameTenant($user, $project);
    }

    public function submit(User $user, RealEstateProject $project): bool
    {
        return $user->can('real_estate_project.submit') && $this->sameTenant($user, $project);
    }

    public function delete(User $user, RealEstateProject $project): bool
    {
        return $user->can('real_estate_project.delete') && $this->sameTenant($user, $project);
    }

    private function sameTenant(User $user, RealEstateProject $project): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $project->tenant_id;
    }
}
