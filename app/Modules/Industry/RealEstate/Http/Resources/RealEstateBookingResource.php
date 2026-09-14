<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\RealEstateBooking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RealEstateBooking
 */
class RealEstateBookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'unit_id' => $this->unit_id,
            'accepted_offer_id' => $this->accepted_offer_id,
            'customer_id' => $this->customer_id,
            'agreed_price' => $this->agreed_price,
            'status' => $this->status->value,
            'booked_at' => $this->booked_at?->toIso8601String(),
            'lead' => $this->whenLoaded('lead', fn () => ['id' => $this->lead->id, 'name' => $this->lead->name]),
            'unit' => $this->whenLoaded('unit', fn () => UnitSummaryResource::make($this->unit)),
            'installment_plan' => new InstallmentPlanResource($this->whenLoaded('installmentPlan')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
