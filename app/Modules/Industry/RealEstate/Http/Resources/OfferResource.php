<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Offer
 */
class OfferResource extends JsonResource
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
            'previous_offer_id' => $this->previous_offer_id,
            'offered_price' => $this->offered_price,
            'offered_by' => $this->offered_by->value,
            'status' => $this->status->value,
            'notes' => $this->notes,
            'lead' => $this->whenLoaded('lead', fn () => ['id' => $this->lead->id, 'name' => $this->lead->name]),
            'unit' => $this->whenLoaded('unit', fn () => UnitSummaryResource::make($this->unit)),
            'counter_offers' => self::collection($this->whenLoaded('counterOffers')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
