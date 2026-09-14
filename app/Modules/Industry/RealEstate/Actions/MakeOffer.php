<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Models\User;
use App\Modules\CRM\Actions\MoveLeadStage;
use App\Modules\CRM\Data\LeadStageData;
use App\Modules\CRM\Domain\LeadStage;
use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\MakeOfferData;
use App\Modules\Industry\RealEstate\Domain\OfferStatus;
use App\Modules\Industry\RealEstate\Models\Offer;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Opens a negotiation chain for a lead against a unit and moves the lead's
 * CRM pipeline stage to `negotiation` — through CRM's own {@see MoveLeadStage}
 * action, never by mutating {@see Lead} directly.
 */
class MakeOffer
{
    use InteractsWithTenant;

    public function __construct(
        private readonly TenantContext $context,
        private readonly MoveLeadStage $moveLeadStage,
    ) {}

    public function handle(Lead $lead, MakeOfferData $data, User $actor): Offer
    {
        $this->assertTenantOwns($lead);
        $unit = $this->assertOwnedModel($data->unitId, Unit::class);

        return DB::transaction(function () use ($lead, $unit, $data): Offer {
            $offer = new Offer([
                'offered_price' => $data->offeredPrice,
                'offered_by' => $data->offeredBy,
                'notes' => $data->notes,
            ]);
            $offer->tenant_id = (int) $lead->tenant_id;
            $offer->lead_id = $lead->getKey();
            $offer->unit_id = $unit->getKey();
            $offer->status = OfferStatus::Pending;
            $offer->save();

            if ($lead->stage->isOpen() && $lead->stage !== LeadStage::Negotiation) {
                $this->moveLeadStage->handle($lead, new LeadStageData(stage: LeadStage::Negotiation, lostReason: null));
            }

            return $offer->refresh();
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
