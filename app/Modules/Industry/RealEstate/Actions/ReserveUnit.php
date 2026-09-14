<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Models\User;
use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\BookingStatus;
use App\Modules\Industry\RealEstate\Domain\OfferStatus;
use App\Modules\Industry\RealEstate\Domain\UnitStatus;
use App\Modules\Industry\RealEstate\Models\Offer;
use App\Modules\Industry\RealEstate\Models\RealEstateBooking;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Turns an accepted offer into a reservation: locks the unit (`reserved`) and
 * opens the booking record. {@see ConfirmBooking} later finalises the sale.
 */
class ReserveUnit
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Offer $acceptedOffer, User $actor): RealEstateBooking
    {
        $this->assertTenantOwns($acceptedOffer);

        if ($acceptedOffer->status !== OfferStatus::Accepted) {
            throw ValidationException::withMessages([
                'offer' => 'Only an accepted offer can be reserved against.',
            ]);
        }

        if (RealEstateBooking::query()->where('accepted_offer_id', $acceptedOffer->getKey())->exists()) {
            throw ValidationException::withMessages([
                'offer' => 'This offer has already been reserved.',
            ]);
        }

        $unit = $acceptedOffer->unit()->firstOrFail();

        if ($unit->status !== UnitStatus::Available) {
            throw ValidationException::withMessages([
                'unit' => "This unit is currently {$unit->status->value} and cannot be reserved.",
            ]);
        }

        return DB::transaction(function () use ($acceptedOffer, $unit): RealEstateBooking {
            $booking = new RealEstateBooking([
                'agreed_price' => $acceptedOffer->offered_price,
            ]);
            $booking->tenant_id = (int) $acceptedOffer->tenant_id;
            $booking->lead_id = $acceptedOffer->lead_id;
            $booking->unit_id = $acceptedOffer->unit_id;
            $booking->accepted_offer_id = $acceptedOffer->getKey();
            $booking->status = BookingStatus::Reserved;
            $booking->save();

            $unit->status = UnitStatus::Reserved;
            $unit->save();

            return $booking->refresh();
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
