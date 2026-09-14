<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\Amenity;

class AmenityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('amenity.view');
    }

    public function view(User $user, Amenity $amenity): bool
    {
        return $user->can('amenity.view') && $this->sameTenant($user, $amenity);
    }

    public function create(User $user): bool
    {
        return $user->can('amenity.create');
    }

    public function delete(User $user, Amenity $amenity): bool
    {
        return $user->can('amenity.delete') && $this->sameTenant($user, $amenity);
    }

    private function sameTenant(User $user, Amenity $amenity): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $amenity->tenant_id;
    }
}
