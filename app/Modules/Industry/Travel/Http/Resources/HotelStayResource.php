<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Resources;

use App\Modules\Industry\Travel\Models\HotelStay;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HotelStay
 */
class HotelStayResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'hotel_name' => $this->hotel_name,
            'city' => $this->city,
            'country' => $this->country,
            'check_in' => $this->check_in->toDateString(),
            'check_out' => $this->check_out->toDateString(),
            'nights' => $this->nights,
            'room_type' => $this->room_type,
            'rooms' => $this->rooms,
            'guests' => $this->guests,
            'board_basis' => $this->board_basis->value,
            'confirmation_no' => $this->confirmation_no,
            'note' => $this->note,
        ];
    }
}
