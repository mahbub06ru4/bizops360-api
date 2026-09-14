<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\OfferStatus;
use App\Modules\Industry\RealEstate\Models\Offer;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class RejectOffer
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Offer $offer): Offer
    {
        $this->assertTenantOwns($offer);

        if (! $offer->status->isOpen()) {
            throw ValidationException::withMessages([
                'offer' => "A {$offer->status->value} offer cannot be rejected.",
            ]);
        }

        $offer->status = OfferStatus::Rejected;
        $offer->save();

        return $offer->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
