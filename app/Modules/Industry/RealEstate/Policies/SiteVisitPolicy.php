<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\SiteVisit;

class SiteVisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('site_visit.view');
    }

    public function view(User $user, SiteVisit $visit): bool
    {
        return $user->can('site_visit.view') && $this->sameTenant($user, $visit);
    }

    public function create(User $user): bool
    {
        return $user->can('site_visit.create');
    }

    public function update(User $user, SiteVisit $visit): bool
    {
        return $user->can('site_visit.update') && $this->sameTenant($user, $visit);
    }

    private function sameTenant(User $user, SiteVisit $visit): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $visit->tenant_id;
    }
}
