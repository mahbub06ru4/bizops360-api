<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CancelBooking
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Booking $booking, ?string $reason = null): Booking
    {
        $this->assertTenantOwns($booking);

        if ($booking->status->isCancelled()) {
            throw ValidationException::withMessages([
                'booking' => "This booking is already {$booking->status->value}.",
            ]);
        }

        $booking->status = BookingStatus::Cancelled;
        $booking->cancelled_on = Carbon::now();

        if ($reason !== null) {
            $booking->notes = trim(($booking->notes ?? '')."\nCancelled: {$reason}");
        }

        $booking->save();

        return $booking->refresh()->load(['customer', 'passengers.traveller', 'segments', 'hotelStays', 'itinerary']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
