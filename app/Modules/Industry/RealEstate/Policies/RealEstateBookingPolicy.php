<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\RealEstateBooking;

class RealEstateBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('real_estate_booking.view');
    }

    public function view(User $user, RealEstateBooking $booking): bool
    {
        return $user->can('real_estate_booking.view') && $this->sameTenant($user, $booking);
    }

    public function create(User $user): bool
    {
        return $user->can('real_estate_booking.create');
    }

    public function update(User $user, RealEstateBooking $booking): bool
    {
        return $user->can('real_estate_booking.update') && $this->sameTenant($user, $booking);
    }

    private function sameTenant(User $user, RealEstateBooking $booking): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $booking->tenant_id;
    }
}
