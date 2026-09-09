<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Confirms a booking with the supplier. Air tickets move to `ticketed` and
 * require a PNR; everything else moves to `confirmed`.
 */
class IssueBooking
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Booking $booking, ?string $pnr = null, ?string $issuedOn = null): Booking
    {
        $this->assertTenantOwns($booking);

        if (! $booking->status->isEditable()) {
            throw ValidationException::withMessages([
                'booking' => "A {$booking->status->value} booking cannot be issued.",
            ]);
        }

        if ($pnr !== null && $pnr !== '') {
            $booking->pnr = $pnr;
        }

        if ($booking->type->requiresPnr() && ($booking->pnr === null || $booking->pnr === '')) {
            throw ValidationException::withMessages([
                'pnr' => 'An air ticket needs a PNR before it can be issued.',
            ]);
        }

        $booking->status = $booking->type->requiresPnr() ? BookingStatus::Ticketed : BookingStatus::Confirmed;
        $booking->issued_on = Carbon::parse($issuedOn ?? Carbon::now()->toDateString());
        $booking->save();

        return $booking->refresh()->load(['customer', 'passengers.traveller', 'segments', 'hotelStays', 'itinerary']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
