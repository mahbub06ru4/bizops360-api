<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions\Concerns;

use App\Modules\Industry\Travel\Data\BookingData;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\Traveller;
use Illuminate\Validation\ValidationException;

/**
 * Rewrites a booking's nested passenger / segment / hotel / itinerary rows from
 * a {@see BookingData}. Every referenced traveller is checked against the
 * booking's tenant first.
 */
trait WritesBookingChildren
{
    private function syncBookingChildren(Booking $booking, BookingData $data): void
    {
        $tenantId = (int) $booking->tenant_id;

        $this->assertTravellersOwned($tenantId, $data);

        $booking->passengers()->delete();
        foreach ($data->passengers as $passenger) {
            $row = $booking->passengers()->make($passenger->toAttributes());
            $row->tenant_id = $tenantId;
            $row->save();
        }

        $booking->segments()->delete();
        foreach ($data->segments as $segment) {
            $row = $booking->segments()->make($segment->toAttributes());
            $row->tenant_id = $tenantId;
            $row->save();
        }

        $booking->hotelStays()->delete();
        foreach ($data->hotelStays as $stay) {
            $row = $booking->hotelStays()->make($stay->toAttributes());
            $row->tenant_id = $tenantId;
            $row->save();
        }

        $booking->itinerary()->delete();
        foreach ($data->itinerary as $item) {
            $row = $booking->itinerary()->make($item->toAttributes());
            $row->tenant_id = $tenantId;
            $row->save();
        }
    }

    private function assertTravellersOwned(int $tenantId, BookingData $data): void
    {
        $ids = array_values(array_unique(array_map(
            static fn ($passenger): int => $passenger->travellerId,
            $data->passengers,
        )));

        if ($ids === []) {
            return;
        }

        $owned = Traveller::query()
            ->whereIn('id', $ids)
            ->where('tenant_id', $tenantId)
            ->count();

        if ($owned !== count($ids)) {
            throw ValidationException::withMessages([
                'passengers' => 'One or more passengers do not belong to this tenant.',
            ]);
        }
    }
}
