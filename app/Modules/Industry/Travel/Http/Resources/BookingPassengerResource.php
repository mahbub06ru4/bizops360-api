<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Resources;

use App\Modules\Industry\Travel\Models\BookingPassenger;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BookingPassenger
 */
class BookingPassengerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'traveller_id' => $this->traveller_id,
            'ticket_number' => $this->ticket_number,
            'baggage' => $this->baggage,
            'fare_amount' => $this->fare_amount,
            'traveller' => new TravellerResource($this->whenLoaded('traveller')),
        ];
    }
}
