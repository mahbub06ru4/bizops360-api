<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Policies;

use App\Models\User;
use App\Modules\Industry\Travel\Models\Booking;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('booking.view');
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->can('booking.view')
            && $this->sameTenant($user, $booking)
            && ($user->can('booking.view_all') || $this->isInvolved($user, $booking));
    }

    public function create(User $user): bool
    {
        return $user->can('booking.create');
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->can('booking.update')
            && $this->sameTenant($user, $booking)
            && ($user->can('booking.view_all') || $this->isInvolved($user, $booking));
    }

    public function issue(User $user, Booking $booking): bool
    {
        return $user->can('booking.issue') && $this->sameTenant($user, $booking);
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->can('booking.cancel') && $this->sameTenant($user, $booking);
    }

    public function refund(User $user, Booking $booking): bool
    {
        return $user->can('booking.refund') && $this->sameTenant($user, $booking);
    }

    public function invoice(User $user, Booking $booking): bool
    {
        return $user->can('booking.invoice') && $this->sameTenant($user, $booking);
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->can('booking.delete') && $this->sameTenant($user, $booking);
    }

    private function isInvolved(User $user, Booking $booking): bool
    {
        if ($booking->created_by === $user->getKey()) {
            return true;
        }

        return $booking->handler !== null && $booking->handler->user_id === $user->getKey();
    }

    private function sameTenant(User $user, Booking $booking): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $booking->tenant_id;
    }
}
