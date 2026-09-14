<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\CounterOfferData;
use App\Modules\Industry\RealEstate\Domain\OfferStatus;
use App\Modules\Industry\RealEstate\Models\Offer;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Responds to an open offer with a counter — never mutates the offer being
 * responded to (beyond marking it `countered`); the new terms are a new row
 * pointing back at it, preserving the full negotiation history.
 */
class CounterOffer
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Offer $previousOffer, CounterOfferData $data): Offer
    {
        $this->assertTenantOwns($previousOffer);

        if (! $previousOffer->status->isOpen()) {
            throw ValidationException::withMessages([
                'offer' => "A {$previousOffer->status->value} offer cannot be countered.",
            ]);
        }

        return DB::transaction(function () use ($previousOffer, $data): Offer {
            $counter = new Offer([
                'offered_price' => $data->offeredPrice,
                'offered_by' => $data->offeredBy,
                'notes' => $data->notes,
            ]);
            $counter->tenant_id = (int) $previousOffer->tenant_id;
            $counter->lead_id = $previousOffer->lead_id;
            $counter->unit_id = $previousOffer->unit_id;
            $counter->previous_offer_id = $previousOffer->getKey();
            $counter->status = OfferStatus::Pending;
            $counter->save();

            $previousOffer->status = OfferStatus::Countered;
            $previousOffer->save();

            return $counter->refresh();
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
