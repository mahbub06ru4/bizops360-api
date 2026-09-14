<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\Offer;

class OfferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('offer.view');
    }

    public function view(User $user, Offer $offer): bool
    {
        return $user->can('offer.view') && $this->sameTenant($user, $offer);
    }

    public function create(User $user): bool
    {
        return $user->can('offer.create');
    }

    public function update(User $user, Offer $offer): bool
    {
        return $user->can('offer.update') && $this->sameTenant($user, $offer);
    }

    private function sameTenant(User $user, Offer $offer): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $offer->tenant_id;
    }
}
