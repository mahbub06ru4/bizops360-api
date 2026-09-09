<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Resources;

use App\Modules\CRM\Http\Resources\CustomerResource;
use App\Modules\Industry\Travel\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Booking
 */
class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'customer_id' => $this->customer_id,
            'handled_by_employee_id' => $this->handled_by_employee_id,
            'invoice_id' => $this->invoice_id,
            'type' => $this->type->value,
            'title' => $this->title,
            'supplier_name' => $this->supplier_name,
            'pnr' => $this->pnr,
            'airline' => $this->airline,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'depart_on' => $this->depart_on?->toDateString(),
            'return_on' => $this->return_on?->toDateString(),
            'status' => $this->status->value,
            'cost_amount' => $this->cost_amount,
            'sell_amount' => $this->sell_amount,
            'commission_amount' => $this->commission_amount,
            'refund_amount' => $this->refund_amount,
            'currency' => $this->currency,
            'issued_on' => $this->issued_on?->toDateString(),
            'cancelled_on' => $this->cancelled_on?->toDateString(),
            'refund_on' => $this->refund_on?->toDateString(),
            'profit' => $this->profit()->toArray(),
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'passengers' => BookingPassengerResource::collection($this->whenLoaded('passengers')),
            'segments' => BookingSegmentResource::collection($this->whenLoaded('segments')),
            'hotel_stays' => HotelStayResource::collection($this->whenLoaded('hotelStays')),
            'itinerary' => PackageItineraryItemResource::collection($this->whenLoaded('itinerary')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
