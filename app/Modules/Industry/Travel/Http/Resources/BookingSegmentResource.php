<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Resources;

use App\Modules\Industry\Travel\Models\BookingSegment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BookingSegment
 */
class BookingSegmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'sequence' => $this->sequence,
            'flight_number' => $this->flight_number,
            'airline' => $this->airline,
            'from_airport' => $this->from_airport,
            'to_airport' => $this->to_airport,
            'depart_at' => $this->depart_at->toIso8601String(),
            'arrive_at' => $this->arrive_at?->toIso8601String(),
            'cabin' => $this->cabin,
        ];
    }
}
